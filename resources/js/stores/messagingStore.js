import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useMessagingStore = defineStore('messaging', () => {
    // State
    const conversations = ref([]);
    const activeConversationId = ref(null);
    const messages = ref([]);
    const unreadCount = ref(0);
    const isLoadingConversations = ref(false);
    const isLoadingMessages = ref(false);
    const isInitialized = ref(false);
    const currentPage = ref(1);
    const hasMoreMessages = ref(false);

    // Track latest message arrival for global Toast notification
    const newIncomingMessage = ref(null);

    // Keep track of active Echo subscriptions to avoid duplicates (conversation ID → channel)
    const activeSubscriptions = ref(new Map());

    // Getters
    const activeConversation = computed(() =>
        conversations.value.find(c => c.id === activeConversationId.value)
    );

    // Actions

    /**
     * Fetch all conversations for the authenticated user.
     * Guard against concurrent calls to avoid duplicate subscriptions.
     */
    async function fetchConversations() {
        if (isLoadingConversations.value) return;

        isLoadingConversations.value = true;
        try {
            const { data } = await axios.get(route('messaging.conversations.index'));
            conversations.value = data.conversations;
            calculateUnreadCount();
            // Bug 2 fix: subscribe after data is ready, with connection readiness check
            setupEchoSubscriptions();
        } catch (error) {
            console.error('Failed to fetch conversations:', error);
        } finally {
            isLoadingConversations.value = false;
        }
    }

    /**
     * Initialize the store once. Idempotent — subsequent calls are no-ops.
     * This prevents Bug 8 (double init from Home.vue and MessagingPanel.vue).
     */
    async function initialize(user) {
        if (isInitialized.value) return;
        isInitialized.value = true;
        setAuthUser(user);
        await fetchConversations();
    }

    async function fetchMessages(conversationId) {
        currentPage.value = 1;
        hasMoreMessages.value = false;
        isLoadingMessages.value = true;
        try {
            const { data } = await axios.get(route('messaging.conversations.show', conversationId));
            // Pagination returns newest first; reverse for chronological display
            messages.value = data.messages.data.reverse();
            currentPage.value = data.messages.current_page;
            hasMoreMessages.value = data.messages.current_page < data.messages.last_page;
            // Merge fresh participant data (pivot.last_read_at may have changed)
            const idx = conversations.value.findIndex(c => c.id === conversationId);
            if (idx !== -1) {
                conversations.value[idx] = Object.assign(conversations.value[idx], data.conversation);
            }
            // Mark as read immediately when opened; await so unread count is accurate
            await markAsRead(conversationId);
        } catch (error) {
            console.error('Failed to fetch messages:', error);
        } finally {
            isLoadingMessages.value = false;
        }
    }

    async function sendMessage(conversationId, body) {
        try {
            const { data } = await axios.post(route('messaging.messages.store', conversationId), { body });
            messages.value.push(data.data);
            // Bring conversation to top and update preview
            updateConversationPreview(conversationId, data.data, true);
        } catch (error) {
            console.error('Failed to send message:', error);
            throw error;
        }
    }

    async function createConversation(participantIds, name = null, isGroup = false) {
        try {
            const { data } = await axios.post(route('messaging.conversations.store'), {
                participant_ids: participantIds,
                name,
                is_group: isGroup
            });

            // Replace or prepend conversation in list
            const existingIdx = conversations.value.findIndex(c => c.id === data.conversation.id);
            if (existingIdx !== -1) {
                conversations.value.splice(existingIdx, 1);
            }
            conversations.value.unshift(data.conversation);

            // Subscribe to the new channel immediately
            subscribeToConversation(data.conversation.id);

            return data.conversation;
        } catch (error) {
            console.error('Failed to create conversation:', error);
            throw error;
        }
    }

    /**
     * Mark a conversation as read. Uses the server-returned timestamp (Bug 5 fix)
     * so we never rely on the browser clock drifting against the server.
     */
    async function markAsRead(conversationId) {
        try {
            const { data } = await axios.post(route('messaging.conversations.read', conversationId));
            const c = conversations.value.find(c => c.id === conversationId);
            if (c && data.last_read_at) {
                // Bug 5a fix: use the authoritative server timestamp, not new Date().toISOString()
                const myId = window.Laravel?.user?.id;
                const me = c.participants.find(p => p.id === myId);
                if (me) {
                    me.pivot.last_read_at = data.last_read_at;
                }
            }
            // Bug 6 fix: recalculate only after the pivot has been updated above
            calculateUnreadCount();
        } catch (error) {
            console.error('Failed to mark as read:', error);
        }
    }

    /**
     * Handle an incoming broadcast message event.
     */
    function handleIncomingMessage(event) {
        const { message, conversation_id } = event;

        if (activeConversationId.value === conversation_id) {
            // Active conversation: append message and immediately mark as read
            messages.value.push(message);
            // Bug 6 fix: markAsRead updates the pivot then recalculates — call it after push
            markAsRead(conversation_id);
        } else {
            // Background conversation: trigger global toast
            newIncomingMessage.value = {
                message,
                conversationId: conversation_id
            };
        }

        // isRead arg = true only when this IS the active conversation (sender already marked read above)
        updateConversationPreview(conversation_id, message, activeConversationId.value === conversation_id);
    }

    /**
     * Bug 2 fix: Wait for Echo's WebSocket to be connected before subscribing.
     * Pusher/Reverb emits 'connected' on the connector; if already connected, subscribe immediately.
     */
    function setupEchoSubscriptions() {
        if (!window.Echo) return;

        const doSubscribe = () => {
            conversations.value.forEach(c => subscribeToConversation(c.id));
        };

        const connector = window.Echo.connector;
        // Reverb/Pusher connector exposes the underlying pusher instance
        const pusher = connector?.pusher ?? connector?.socket ?? null;

        if (pusher && pusher.connection?.state === 'connected') {
            // Already connected — subscribe immediately
            doSubscribe();
        } else if (pusher) {
            // Wait for the connection to be established, then subscribe
            pusher.connection.bind('connected', () => {
                doSubscribe();
                pusher.connection.unbind('connected');
            });
        } else {
            // Fallback: try anyway after a short delay
            setTimeout(doSubscribe, 1000);
        }
    }

    /**
     * Subscribe to a single conversation channel if not already subscribed.
     * Bug 9 fix: stores the channel reference in a Map so we can leave it later.
     */
    function subscribeToConversation(id) {
        if (!window.Echo || activeSubscriptions.value.has(id)) return;

        const channel = window.Echo.private(`conversation.${id}`)
            .listen('.message.sent', (event) => {
                handleIncomingMessage(event);
            })
            .listen('.message.read', (event) => {
                // Update the pivot for the reading user so unread indicators refresh
                handleReadReceipt(event);
            });

        // Store the channel reference for cleanup
        activeSubscriptions.value.set(id, channel);
    }

    /**
     * Handle a read receipt broadcast from another participant.
     */
    function handleReadReceipt(event) {
        const { user_id, conversation_id, last_read_at } = event;
        const c = conversations.value.find(c => c.id === conversation_id);
        if (!c) return;
        const participant = c.participants.find(p => p.id === user_id);
        if (participant) {
            participant.pivot.last_read_at = last_read_at;
        }
        calculateUnreadCount();
    }

    /**
     * Bug 9 fix: Leave all active Echo channels and reset subscription tracking.
     * Call this on component teardown or store reset.
     */
    function cleanup() {
        if (!window.Echo) return;
        activeSubscriptions.value.forEach((channel, id) => {
            window.Echo.leave(`conversation.${id}`);
        });
        activeSubscriptions.value.clear();
    }

    /**
     * Update the conversation's preview data and re-sort the list.
     */
    function updateConversationPreview(conversationId, message, isRead) {
        const idx = conversations.value.findIndex(c => c.id === conversationId);
        if (idx !== -1) {
            const conversation = conversations.value[idx];
            conversation.latest_message = message;
            // Bug 4 fix: normalise to ISO string so Date comparisons are consistent
            conversation.last_message_at = message.created_at;

            // Move to top
            conversations.value.splice(idx, 1);
            conversations.value.unshift(conversation);
        } else {
            // New conversation arrived out-of-band: re-fetch the full list
            fetchConversations();
        }
        // Only recalculate here if we haven't already done it in markAsRead
        if (!isRead) {
            calculateUnreadCount();
        }
    }

    /**
     * Recalculate the global unread count from loaded conversation data.
     * Bug 4 fix: both dates are normalised through `new Date()` before comparison,
     * so ISO 8601 vs MySQL datetime string differences don't matter.
     */
    function calculateUnreadCount() {
        const myId = window.Laravel?.user?.id;
        if (!myId) return;

        let count = 0;
        for (const c of conversations.value) {
            const me = c.participants.find(p => p.id === myId);
            if (!me) continue;

            const messageTime = c.latest_message
                ? new Date(c.latest_message.created_at).getTime()
                : c.last_message_at
                    ? new Date(c.last_message_at).getTime()
                    : 0;
            const readTime = me.pivot?.last_read_at ? new Date(me.pivot.last_read_at).getTime() : 0;

            if (messageTime > readTime) count++;
        }
        unreadCount.value = count;
    }

    function setAuthUser(user) {
        if (!window.Laravel) window.Laravel = {};
        window.Laravel.user = user;
        calculateUnreadCount();
    }

    async function searchContacts(query) {
        if (query.length < 2) return [];
        try {
            const { data } = await axios.get(route('messaging.contacts.search', { q: query }));
            return data.users;
        } catch (error) {
            console.error('Contact search failed:', error);
            return [];
        }
    }

    /**
     * Force a server-side refresh of the unread count and conversation list.
     * Used by the UnreadMessagesDrawer when opened, as a fallback if Echo events were missed.
     */
    async function refreshConversations() {
        try {
            const { data } = await axios.get(route('messaging.conversations.index'));
            conversations.value = data.conversations;
            calculateUnreadCount();
            // Subscribe to any new conversations that may have appeared
            conversations.value.forEach(c => subscribeToConversation(c.id));
        } catch (error) {
            console.error('Failed to refresh conversations:', error);
        }
    }

    async function fetchOlderMessages(conversationId) {
        const nextPage = currentPage.value + 1;
        const response = await axios.get(route('conversations.messages', conversationId), {
            params: { page: nextPage }
        });
        const data = response.data;
        messages.value = [...data.data.reverse(), ...messages.value];
        currentPage.value = data.current_page;
        hasMoreMessages.value = data.current_page < data.last_page;
    }

    return {
        conversations,
        activeConversationId,
        messages,
        unreadCount,
        isLoadingConversations,
        isLoadingMessages,
        isInitialized,
        activeConversation,
        currentPage,
        hasMoreMessages,
        initialize,
        fetchConversations,
        fetchMessages,
        sendMessage,
        createConversation,
        markAsRead,
        handleIncomingMessage,
        setAuthUser,
        searchContacts,
        newIncomingMessage,
        cleanup,
        refreshConversations,
        fetchOlderMessages,
    };
});

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const useTimeTrackingStore = defineStore('timeTracking', () => {
    // â”€â”€ State â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /** The currently running TimeEntry (or null). Hydrated from page.props.activeTimer. */
    const activeTimer = ref(null);

    /** Seconds elapsed since the active timer started (updated every second). */
    const localElapsed = ref(0);

    let elapsedInterval = null;

    // â”€â”€ Getters â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    const hasActiveTimer = computed(() => activeTimer.value !== null);

    const activeTaskId = computed(() => activeTimer.value?.task_id ?? null);

    /**
     * Human-readable elapsed string: "HH:MM:SS".
     * Reads from localElapsed so the display updates live.
     */
    const elapsedFormatted = computed(() => {
        const s = localElapsed.value;
        const h = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        const sec = s % 60;
        return [h, m, sec].map(n => String(n).padStart(2, '0')).join(':');
    });

    // â”€â”€ Private helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    function startElapsedCounter(startedAt) {
        stopElapsedCounter();
        const start = new Date(startedAt).getTime();
        localElapsed.value = Math.floor((Date.now() - start) / 1000);
        elapsedInterval = setInterval(() => {
            localElapsed.value = Math.floor((Date.now() - start) / 1000);
        }, 1000);
    }

    function stopElapsedCounter() {
        if (elapsedInterval) {
            clearInterval(elapsedInterval);
            elapsedInterval = null;
        }
        localElapsed.value = 0;
    }

    // â”€â”€ Actions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Hydrate from page.props.activeTimer.
     * Call once from Home.vue onMounted.
     */
    function initialize() {
        const page = usePage();
        const timer = page.props.activeTimer;
        if (timer) {
            activeTimer.value = timer;
            startElapsedCounter(timer.started_at);
        }
    }

    /**
     * Start a timer on the given task.
     * Returns the new TimeEntry or throws on error.
     */
    async function startTimer(taskId) {
        try {
            const res = await fetch(route('time.start'), {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify({ task_id: taskId }),
            });

            if (!res.ok) {
                const errorData = await res.json().catch(() => ({}));
                console.error('Start Timer Error:', res.status, errorData);
                throw new Error(errorData.message || `Server error: ${res.status}`);
            }

            const { data } = await res.json();
            activeTimer.value = data;
            startElapsedCounter(data.started_at);
            return data;
        } catch (err) {
            console.error('Start Timer Exception:', err);
            throw err;
        }
    }

    /**
     * Stop the active timer.
     * Returns the completed TimeEntry or null if nothing was running.
     */
    async function stopTimer() {
        try {
            const res = await fetch(route('time.stop'), {
                method: 'POST',
                headers: { 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
            });

            if (res.status === 404) {
                activeTimer.value = null;
                stopElapsedCounter();
                return null;
            }

            if (!res.ok) {
                const errorData = await res.json().catch(() => ({}));
                console.error('Stop Timer Error:', res.status, errorData);
                throw new Error(errorData.message || `Server error: ${res.status}`);
            }

            const { data } = await res.json();
            activeTimer.value = null;
            stopElapsedCounter();
            return data;
        } catch (err) {
            console.error('Stop Timer Exception:', err);
            throw err;
        }
    }

    /**
     * Log a manual time entry for a task.
     */
    async function logManual(taskId, payload) {
        try {
            const res = await fetch(route('time.store', { task: taskId }), {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify(payload),
            });

            if (!res.ok) {
                const errorData = await res.json().catch(() => ({}));
                console.error('Log Manual Error:', res.status, errorData);
                throw new Error(errorData.message || `Server error: ${res.status}`);
            }

            return (await res.json()).data;
        } catch (err) {
            console.error('Log Manual Exception:', err);
            throw err;
        }
    }

    /**
     * Update an existing entry.
     */
    async function updateEntry(entryId, payload) {
        const res = await fetch(route('time.update', { entry: entryId }), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify(payload),
        });

        if (!res.ok) throw new Error(await res.text());
        return (await res.json()).data;
    }

    /**
     * Delete an entry.
     */
    async function deleteEntry(entryId) {
        const res = await fetch(route('time.destroy', { entry: entryId }), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        });

        if (!res.ok) throw new Error(await res.text());
    }

    /**
     * Fetch report data (personal or team).
     * period: 'month' | 'week' | 'last_month' | 'last_week'
     * team: boolean
     */
    async function fetchReport(period = 'month', team = false) {
        const url = route('time.report') + `?period=${period}&team=${team ? 1 : 0}`;
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json' },
        });

        if (!res.ok) throw new Error(await res.text());
        return res.json();
    }

    return {
        // state
        activeTimer,
        localElapsed,
        // getters
        hasActiveTimer,
        activeTaskId,
        elapsedFormatted,
        // actions
        initialize,
        startTimer,
        stopTimer,
        logManual,
        updateEntry,
        deleteEntry,
        fetchReport,
    };
});

@component('mail::message')
    <div style="text-align: center;">
        <img src="{{ asset('img/teamPulse_logo_artwork.png') }}" width="150" alt="Logo">
    </div>
    # Ti hanno invitato a collaborare!

    Ciao,
    Sei stato invitato ad unirti al team {{ $invitation->team->name }} con il ruolo di {{ ucfirst($invitation->role) }}.

    Per accettare l'invito e iniziare a collaborare ai progetti, clicca sul pulsante qui sotto:

    @component('mail::button', ['url' => $url])
        Accetta Invito
    @endcomponent

    *Questo link scadrà il {{ $invitation->expires_at->format('d/m/Y') }}.*

    Se non ti aspettavi questo invito, puoi ignorare questa email.

    Grazie,
    {{ config('app.name') }}
@endcomponent

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invito al Team</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding: 40px 0;">
                            <img src="{{ $message->embed(public_path('img/teamPulse_logo_artwork.png')) }}" width="150" alt="TeamPulse Logo">
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 0 40px;">
                            <h1 style="font-size: 24px; color: #333333; text-align: center;">Ti hanno invitato a collaborare!</h1>
                            <p style="font-size: 16px; color: #555555; line-height: 1.5;">
                                Ciao,
                            </p>
                            <p style="font-size: 16px; color: #555555; line-height: 1.5;">
                                Sei stato invitato ad unirti al team <strong>{{ $invitation->team->name }}</strong> con il ruolo di <strong>{{ ucfirst($invitation->role) }}</strong>.
                            </p>
                            <p style="font-size: 16px; color: #555555; line-height: 1.5;">
                                Per accettare l'invito e iniziare a collaborare ai progetti, clicca sul pulsante qui sotto:
                            </p>
                        </td>
                    </tr>
                    <!-- Button -->
                    <tr>
                        <td align="center" style="padding: 30px 0;">
                            <a href="{{ $url }}" style="background-color: #4a90e2; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold; display: inline-block;">Accetta Invito</a>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 0 40px;">
                            <p style="font-size: 14px; color: #888888; text-align: center; line-height: 1.5;">
                                <em>Questo link scadrà il {{ $invitation->expires_at->format('d/m/Y') }}.</em>
                            </p>
                            <p style="font-size: 14px; color: #888888; text-align: center; line-height: 1.5;">
                                Se non ti aspettavi questo invito, puoi ignorare questa email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 40px 0; font-size: 14px; color: #888888;">
                            Grazie,<br>
                            {{ config('app.name') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

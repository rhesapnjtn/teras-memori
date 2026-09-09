import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const echo = new Echo({
    broadcaster: 'reverb',

    key: import.meta.env.VITE_REVERB_APP_KEY,

    wsHost:
        import.meta.env.VITE_REVERB_HOST ||
        window.location.hostname,

    wsPort: Number(
        import.meta.env.VITE_REVERB_PORT || 8080
    ),

    wssPort: Number(
        import.meta.env.VITE_REVERB_PORT || 8080
    ),

    forceTLS: false,

    enabledTransports: ['ws', 'wss'],

    /*
    |--------------------------------------------------------------------------
    | Private Channel Authentication
    |--------------------------------------------------------------------------
    */

    authEndpoint: '/broadcasting/auth',

    auth: {
        headers: {
            Accept: 'application/json',
        },
    },

    authorizer: (channel) => {
        return {
            authorize: (socketId, callback) => {
                const token =
                    localStorage.getItem('auth_token')

                fetch('/broadcasting/auth', {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json',

                        Accept:
                            'application/json',

                        ...(token
                            ? {
                                  Authorization:
                                      `Bearer ${token}`,
                              }
                            : {}),
                    },

                    body: JSON.stringify({
                        socket_id: socketId,
                        channel_name: channel.name,
                    }),
                })
                    .then(async (response) => {
                        const data =
                            await response.json()

                        if (!response.ok) {
                            throw new Error(
                                data.message ||
                                    'Broadcast authentication failed.'
                            )
                        }

                        callback(false, data)
                    })
                    .catch((error) => {
                        console.error(
                            'Broadcast authentication error:',
                            error
                        )

                        callback(true, error)
                    })
            },
        }
    },
})

export default echo

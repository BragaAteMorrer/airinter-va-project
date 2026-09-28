(() => {
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    const b64url = (buffer) => {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (const byte of bytes) binary += String.fromCharCode(byte);
        return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
    };

    const decode = (value) => {
        const padded = value.replace(/-/g, '+').replace(/_/g, '/') + '==='.slice((value.length + 3) % 4);
        const binary = atob(padded);
        return Uint8Array.from(binary, c => c.charCodeAt(0));
    };

    const creationOptions = (json) => {
        if (window.PublicKeyCredential?.parseCreationOptionsFromJSON) {
            return PublicKeyCredential.parseCreationOptionsFromJSON(json);
        }

        const copy = structuredClone(json);
        copy.challenge = decode(copy.challenge);
        copy.user.id = decode(copy.user.id);
        copy.excludeCredentials = (copy.excludeCredentials || []).map(item => ({...item, id: decode(item.id)}));
        return copy;
    };

    const requestOptions = (json) => {
        if (window.PublicKeyCredential?.parseRequestOptionsFromJSON) {
            return PublicKeyCredential.parseRequestOptionsFromJSON(json);
        }

        const copy = structuredClone(json);
        copy.challenge = decode(copy.challenge);
        copy.allowCredentials = (copy.allowCredentials || []).map(item => ({...item, id: decode(item.id)}));
        return copy;
    };

    const serialize = (credential) => {
        if (typeof credential.toJSON === 'function') return credential.toJSON();

        const response = credential.response;
        const payload = {
            id: credential.id,
            rawId: b64url(credential.rawId),
            type: credential.type,
            authenticatorAttachment: credential.authenticatorAttachment ?? null,
            clientExtensionResults: credential.getClientExtensionResults(),
            response: {
                clientDataJSON: b64url(response.clientDataJSON),
            },
        };

        if ('attestationObject' in response) {
            payload.response.attestationObject = b64url(response.attestationObject);
            payload.response.transports = typeof response.getTransports === 'function' ? response.getTransports() : [];
        } else {
            payload.response.authenticatorData = b64url(response.authenticatorData);
            payload.response.signature = b64url(response.signature);
            payload.response.userHandle = response.userHandle ? b64url(response.userHandle) : null;
        }

        return payload;
    };

    const api = async (url, options = {}) => {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
            ...options,
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || data.error || 'La passkey n’a pas pu être utilisée.');
        }

        return data;
    };

    async function login(button) {
        if (!window.PublicKeyCredential || !navigator.credentials) throw new Error('Passkeys non prises en charge par ce navigateur.');

        button.disabled = true;
        try {
            const data = await api('/passkeys/login/options', {method: 'GET'});
            const credential = await navigator.credentials.get({publicKey: requestOptions(data.options)});
            const remember = !!document.querySelector('input[name="remember"]')?.checked;
            const result = await api('/passkeys/login', {
                method: 'POST',
                body: JSON.stringify({credential: serialize(credential), remember}),
            });
            window.location.href = result.redirect || '/account';
        } finally {
            button.disabled = false;
        }
    }

    async function register(button) {
        if (!window.PublicKeyCredential || !navigator.credentials) throw new Error('Passkeys non prises en charge par ce navigateur.');

        const name = window.prompt('Nom de cette passkey (ex. PC maison, iPhone, YubiKey) :');
        if (!name) return;

        button.disabled = true;
        try {
            const data = await api('/user/passkeys/options', {method: 'GET'});
            const credential = await navigator.credentials.create({publicKey: creationOptions(data.options)});
            await api('/user/passkeys', {
                method: 'POST',
                body: JSON.stringify({name, credential: serialize(credential)}),
            });
            window.location.reload();
        } finally {
            button.disabled = false;
        }
    }

    document.addEventListener('click', async (event) => {
        const loginButton = event.target.closest('[data-passkey-login]');
        const registerButton = event.target.closest('[data-passkey-register]');
        if (!loginButton && !registerButton) return;

        event.preventDefault();
        const button = loginButton || registerButton;
        const output = document.querySelector('[data-passkey-error]');

        try {
            if (loginButton) await login(button);
            else await register(button);
        } catch (error) {
            if (output) output.textContent = error?.message || 'Erreur passkey.';
        }
    });
})();

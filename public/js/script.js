// Function to get token from localStorage
function getToken() {
    return localStorage.getItem('token');
}

// Secure fetch wrapper
async function secureFetch(url, options = {}) {
    options = options || {};
    options.headers = {
        ...(options.headers || {}),
        'Accept': 'application/json'
    };

    const token = getToken();

    if (token) {
        options.headers.Authorization = `Bearer ${token}`;
    }

    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
        options.body = JSON.stringify(options.body);
        options.headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(url, options);

        if (response.status === 404) {
          return null;
        }


    return response.json();
}

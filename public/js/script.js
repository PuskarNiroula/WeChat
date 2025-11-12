// Function to get token from localStorage
function getToken() {
    return localStorage.getItem('token');
}

// Secure fetch wrapper
async function secureFetch(url, options = {}) {
    options = options || {}
    options.headers = { ...(options.headers || {}) }

    const token = getToken()
    if (token) options.headers.Authorization = `Bearer ${token}`

    // Only stringify if it's a plain object, leave FormData as-is
    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
        options.body = JSON.stringify(options.body)
        options.headers['Content-Type'] = 'application/json'
    }

    const response = await fetch(url, options)
    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}))
        throw new Error(errorData.message || 'Request failed')
    }
    return response.json()
}

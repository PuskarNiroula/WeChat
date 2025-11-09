// Function to get token from localStorage
function getToken() {
    return localStorage.getItem('token');
}

// Secure fetch wrapper
async function secureFetch(url, options = {}) {
    // Ensure options object exists
    options = options || {};

    // Default headers
    options.headers = options.headers || {};

    // Add token to Authorization header if exists
    const token = getToken();
    if (token) {
        options.headers['Authorization'] = `Bearer ${token}`;
    }

    // If body is an object, stringify it and set Content-Type
    if (options.body && typeof options.body === 'object') {
        options.body = JSON.stringify(options.body);
        options.headers['Content-Type'] = 'application/json';
    }

    // Perform fetch
    const response = await fetch(url, options);

    // Check for errors
    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Request failed');
    }

    // Return JSON
    return response.json();
}

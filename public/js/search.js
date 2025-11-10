// Debounce function: delays execution until user stops typing
function debounce(fn, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

// Search function that calls the API using secureFetch
async function search(query) {
    if (!query) {
        resultsContainer.style.display = 'none';
        resultsContainer.innerHTML = '';
        return;
    }
    resultsContainer.innerHTML = '<div class="px-3 py-2 text-muted">Searching...</div>';
    resultsContainer.style.display = 'block';

    try {
        const results = await secureFetch(`/search/${encodeURIComponent(query)}`, {
            method: 'GET'
        });

        // Clear previous results
        resultsContainer.innerHTML = '';

        if (results.length === 0) {
            resultsContainer.style.display = 'none';
            return;
        }

        // Populate results
        results.forEach(user => {
            const div = document.createElement('div');
            div.textContent = user;
            div.className = 'px-3 py-2 search-item';
            div.style.cursor = 'pointer';

            // Optional: click to select user
            div.addEventListener('click', () => {
                document.getElementById('searchInput').value = user;
                resultsContainer.style.display = 'none';
                console.log('User selected:', user);
            });

            resultsContainer.appendChild(div);
        });

        resultsContainer.style.display = 'block';
    } catch (err) {
        console.error('Search error:', err.message);
    }
}
const resultsContainer = document.getElementById('searchResults');

// Attach debounced input listener
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('input', debounce((e) => {
    search(e.target.value);
}, 500));




searchInput.addEventListener('blur', () => {
    // Small timeout to allow click to register
    setTimeout(() => {
        resultsContainer.style.display = 'none';
    }, 100);
});

searchInput.addEventListener('focus', () => {
    if (searchInput.value.trim() !== '') {
        search(searchInput.value);
    }
});

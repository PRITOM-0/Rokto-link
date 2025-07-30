/**
 * js/search.js
 *
 * This file contains client-side JavaScript for enhancing the donor search functionality.
 * It provides features like live filtering (AJAX-based) and clearing search inputs.
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.search-form');
    const bloodGroupSelect = document.getElementById('blood_group');
    const cityInput = document.getElementById('city');
    const searchResultsContainer = document.querySelector('.results-section');
    const searchButton = document.querySelector('.search-button');

    // Create a clear button dynamically (or add it directly in HTML if preferred)
    const clearButton = document.createElement('button');
    clearButton.type = 'button';
    clearButton.textContent = 'Clear Search';
    clearButton.className = 'clear-button action-button'; // Reusing action-button style
    clearButton.style.marginLeft = '10px'; // Add some spacing

    // Append clear button next to the search button
    if (searchButton && searchButton.parentNode) {
        searchButton.parentNode.insertBefore(clearButton, searchButton.nextSibling);
    }

    /**
     * Fetches donor search results dynamically using AJAX.
     * Updates the search results container with the new HTML.
     */
    function fetchSearchResults() {
        const bloodGroup = bloodGroupSelect.value;
        const city = cityInput.value.trim();

        // Construct query parameters
        const params = new URLSearchParams();
        params.append('search', 'true'); // Indicate that a search is being performed
        if (bloodGroup && bloodGroup !== 'Any') {
            params.append('blood_group', bloodGroup);
        }
        if (city) {
            params.append('city', city);
        }

        // Show a loading indicator
        searchResultsContainer.innerHTML = '<p class="no-results">Searching for donors...</p>';

        fetch(`search_donor.php?${params.toString()}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                // Parse the response HTML to extract the results table
                // This is a simple way; for more complex pages, consider returning JSON
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newResultsContent = doc.querySelector('.results-section');

                if (newResultsContent) {
                    searchResultsContainer.innerHTML = newResultsContent.innerHTML;
                } else {
                    searchResultsContainer.innerHTML = '<p class="no-results">Could not load search results.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching search results:', error);
                searchResultsContainer.innerHTML = '<p class="error-message">Failed to load search results. Please try again.</p>';
            });
    }

    /**
     * Clears the search input fields and re-fetches all donors (or no donors if no criteria).
     */
    function clearSearchFields() {
        bloodGroupSelect.value = 'Any';
        cityInput.value = '';
        fetchSearchResults(); // Fetch results after clearing
    }

    // Event Listeners
    if (searchForm) {
        // Prevent default form submission to handle via AJAX
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            fetchSearchResults();
        });

        // Optional: Trigger search on input change (live search)
        bloodGroupSelect.addEventListener('change', fetchSearchResults);
        cityInput.addEventListener('input', fetchSearchResults); // 'input' event for real-time typing

        // Event listener for the clear button
        clearButton.addEventListener('click', clearSearchFields);

        // Initial fetch when the page loads to display all eligible donors
        fetchSearchResults();
    }
});

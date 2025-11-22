</div>
    </div>
    
    <script>
        // Auto-refresh dashboard every 5 minutes (only for dashboard page)
        if (window.location.pathname.includes('superadmin.php')) {
            setTimeout(function() {
                window.location.reload();
            }, 300000); // 5 minutes
        }

        // Simple search functionality for tables
        function initializeTableSearch(searchInputClass, tableRowsSelector) {
            const searchInput = document.querySelector('.' + searchInputClass);
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll(tableRowsSelector);
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }
        }

        // Filter by role/dropdown
        function initializeDropdownFilter(dropdownClass, columnIndex) {
            const dropdown = document.querySelector('.' + dropdownClass);
            if (dropdown) {
                dropdown.addEventListener('change', function() {
                    const selectedValue = this.value;
                    const rows = document.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        if (selectedValue === '') {
                            row.style.display = '';
                        } else {
                            const cell = row.querySelector('td:nth-child(' + columnIndex + ')');
                            if (cell) {
                                const cellText = cell.textContent.toLowerCase();
                                row.style.display = cellText.includes(selectedValue.toLowerCase()) ? '' : 'none';
                            }
                        }
                    });
                });
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize search for user management
            initializeTableSearch('search-input', 'tbody tr');
            
            // Initialize role filter
            initializeDropdownFilter('filter-dropdown', 4);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = themeToggle.querySelector('.theme-icon');
            const themeText = themeToggle.querySelector('.text');
            
            // Check for saved theme preference or default to dark
            const currentTheme = localStorage.getItem('theme') || 'dark';
            
            // Apply the saved theme
            document.body.classList.toggle('light-theme', currentTheme === 'light');
            updateThemeButton(currentTheme);
            
            // Toggle theme when button is clicked
            themeToggle.addEventListener('click', function() {
                const isLightTheme = document.body.classList.toggle('light-theme');
                const newTheme = isLightTheme ? 'light' : 'dark';
                
                // Save preference to localStorage
                localStorage.setItem('theme', newTheme);
                
                // Update button text and icon
                updateThemeButton(newTheme);
            });
            
            function updateThemeButton(theme) {
                if (theme === 'light') {
                    themeIcon.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f5a623" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';
                    themeText.textContent = 'Light Mode';
                } else {
                    themeIcon.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
                    themeText.textContent = 'Dark Mode';
                }
            }
        });

    </script>
</body>
</html>
<?php
// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>
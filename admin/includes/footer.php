</div>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('open');
        }

        // Theme switching functionality
        class ThemeManager {
            constructor() {
                this.currentTheme = this.getSavedTheme() || 'dark-theme';
                this.init();
            }

            init() {
                this.applyTheme(this.currentTheme);
                this.addThemeToggle();
            }

            getSavedTheme() {
                return localStorage.getItem('preferred-theme');
            }

            saveTheme(theme) {
                localStorage.setItem('preferred-theme', theme);
            }

            applyTheme(theme) {
                document.body.className = theme;
                this.currentTheme = theme;
                this.saveTheme(theme);
                this.updateThemeToggleButton();
            }

            toggleTheme() {
                const newTheme = this.currentTheme === 'dark-theme' ? 'light-theme' : 'dark-theme';
                this.applyTheme(newTheme);
            }

            addThemeToggle() {
                const toggleBtn = document.getElementById('themeToggle');
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', () => this.toggleTheme());
                    this.updateThemeToggleButton();
                }
            }

            updateThemeToggleButton() {
                const toggleBtn = document.getElementById('themeToggle');
                if (toggleBtn) {
                    const moonIcon = toggleBtn.querySelector('.moon-icon');
                    const sunIcon = toggleBtn.querySelector('.sun-icon');
                    const text = toggleBtn.querySelector('.text');
                    
                    if (this.currentTheme === 'dark-theme') {
                        moonIcon.style.display = 'block';
                        sunIcon.style.display = 'none';
                        text.textContent = 'Dark Mode';
                    } else {
                        moonIcon.style.display = 'none';
                        sunIcon.style.display = 'block';
                        text.textContent = 'Light Mode';
                    }
                }
            }
        }

        // Initialize theme manager
        document.addEventListener('DOMContentLoaded', () => {
            new ThemeManager();
        });
    </script>
</body>
</html>
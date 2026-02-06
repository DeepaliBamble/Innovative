            </main>

            <!-- Footer -->
            <footer class="admin-footer">
                <p>&copy; <?= date('Y') ?>  Innovative Homes. All rights reserved.  |  Developed with ❤️ By Web Crafters IT Solutions.</p>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling and other interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight current page in sidebar
            const currentPage = '<?= $current_page ?>';
            const links = document.querySelectorAll('.sidebar-nav a');

            links.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>

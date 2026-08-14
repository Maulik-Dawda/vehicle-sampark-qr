    </main>
    <footer class="footer">
        <div class="footer-container" style="text-align: center; padding: 1.5rem; border-top: 1px solid var(--border-color); font-size: 0.88rem; color: var(--text-muted);">
            <p>&copy; <?= date('Y') ?> <strong style="color: var(--primary);">Vehicle Sampark</strong> &bull; Connecting Mobility &bull; Smart Vehicle QR System</p>
        </div>
    </footer>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- App JavaScript with Cache Buster -->
    <script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>

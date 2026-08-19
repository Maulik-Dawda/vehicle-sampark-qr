    </main>
    <footer class="footer">
        <div class="footer-container" style="text-align: center; padding: 1.5rem; border-top: 1px solid var(--border-color); font-size: 0.88rem; color: var(--text-muted);">
            <p>&copy; <?= date('Y') ?> <strong style="color: var(--primary);">Vehicle Sampark</strong> &bull; Always Within Reach. &bull; Smart Vehicle QR System</p>
        </div>
    </footer>

    <!-- LOGOUT CONFIRMATION MODAL -->
    <div class="modal-backdrop" id="logoutModal">
        <div class="modal-card" style="max-width: 440px; text-align: center; padding: 2.25rem 1.75rem;">
            <div style="width: 64px; height: 64px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1.25rem; border: 2px solid rgba(244, 63, 94, 0.2); animation: pulseLogout 2s infinite;">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;">Confirm Logout</h3>
            <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.5; margin-bottom: 1.75rem;">
                Are you sure you want to end your current session? You will need to log back in to access the Vehicle Sampark admin panel.
            </p>
            <div style="display: flex; gap: 0.75rem; justify-content: center;">
                <button type="button" class="btn btn-secondary modal-close" style="flex: 1; padding: 0.75rem; font-weight: 600;">Cancel</button>
                <a href="admin-qr-login?action=logout" class="btn btn-danger" style="flex: 1; padding: 0.75rem; background: #f43f5e; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; border: none; transition: transform 0.2s, background 0.2s;">
                    <i class="fa-solid fa-right-from-bracket"></i> Yes, Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- App JavaScript with Cache Buster -->
    <script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>

<?php
// File: /siman/include/footer.php
?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toggleBtn = document.getElementById("sidebarToggle");
            const sidebar = document.querySelector(".sidebar");
            const body = document.body;

            toggleBtn.addEventListener("click", function () {
                sidebar.classList.toggle("collapsed");
                body.classList.toggle("sidebar-collapsed");
            });
        });
    </script>
</body>
</html>
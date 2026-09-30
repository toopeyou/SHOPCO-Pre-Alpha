<?php
session_start();
if (isset($_SESSION['name'])) {
} else {
    echo '<div class="small p-2 bg-black text-white p-2 d-flex justify-content-center">
            Sign up and get 20% off to your first order.
            <a href="/singup.php" class="text-white">
                Sign Up Now
            </a>


        </div>';
}
?>
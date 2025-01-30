<!-- Updated profile.php -->
<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT name, email, profile_image FROM users WHERE id=$user_id")->fetch_assoc();
$location = $conn->query("SELECT latitude, longitude FROM locations WHERE user_id=$user_id ORDER BY timestamp DESC LIMIT 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-gradient-to-br from-blue-900 to-purple-900 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Profile Header -->
        <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 mb-6">
            <div class="flex items-center gap-6">
                <div class="relative">
                    <img src="<?php echo $user['profile_image'] ?? '/api/placeholder/150/150'; ?>" 
                         alt="Profile" 
                         class="w-32 h-32 rounded-full object-cover border-4 border-purple-500">
                    <label for="profile-image" class="absolute bottom-0 right-0 bg-purple-500 p-2 rounded-full cursor-pointer">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </label>
                    <input type="file" id="profile-image" class="hidden" accept="image/*">
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2"><?php echo $user['name']; ?></h1>
                    <p class="text-gray-300"><?php echo $user['email']; ?></p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <button class="bg-gradient-to-r from-purple-500 to-purple-600 text-white py-3 px-6 rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all">
                View Orders
            </button>
            <button class="bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-6 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all">
                Edit Profile
            </button>
            <button class="bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-6 rounded-lg hover:from-green-600 hover:to-green-700 transition-all">
                Support
            </button>
        </div>

        <!-- Map Section -->
        <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 mb-6">
            <h2 class="text-2xl font-bold text-white mb-4">Current Location</h2>
            <div id="map" class="h-96 rounded-lg"></div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white/10 backdrop-blur-lg p-8 rounded-2xl shadow-2xl w-96">
            <div class="mb-6">
                <img src="/api/placeholder/250/250" alt="Smart Blindstick" class="w-full rounded-lg mb-4">
                <h2 class="text-2xl font-bold text-white mb-2">Smart Blindstick</h2>
                <p class="text-blue-400 text-xl">$99.99</p>
            </div>
            <form id="payment-form">
                <div id="card-element" class="bg-white/10 rounded-lg p-4 mb-4"></div>
                <div id="card-errors" class="text-red-500 mb-4"></div>
                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-500 text-white py-3 rounded-lg">
                    Pay Now
                </button>
            </form>
            <button onclick="closePaymentModal()" class="mt-4 text-gray-400 hover:text-white">Close</button>
        </div>
    </div>

    <script>
        // Map initialization
        function initMap() {
            const location = { 
                lat: <?php echo $location['latitude'] ?? 0; ?>, 
                lng: <?php echo $location['longitude'] ?? 0; ?> 
            };
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: location,
                styles: [
                    {
                        "elementType": "geometry",
                        "stylers": [{"color": "#242f3e"}]
                    },
                    // Add more custom styles as needed
                ]
            });
            new google.maps.Marker({ position: location, map: map });
        }

        // Profile image upload
        document.getElementById('profile-image').addEventListener('change', async function(e) {
            const file = e.target.files[0];
            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('upload_profile_image.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                }
            } catch (error) {
                console.error('Error uploading image:', error);
            }
        });

        // Payment integration
        const stripe = Stripe('your_publishable_key');
        const elements = stripe.elements();
        const card = elements.create('card', {
            style: {
                base: {
                    color: '#fff',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                }
            }
        });
        card.mount('#card-element');

        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const {token, error} = await stripe.createToken(card);

            if (error) {
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
            } else {
                // Handle the token on your server
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({token: token.id})
                });
                const result = await response.json();
                if (result.success) {
                    alert('Payment successful!');
                    closePaymentModal();
                }
            }
        });

        // Modal functions
        function showPaymentModal() {
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
</body>
</html>
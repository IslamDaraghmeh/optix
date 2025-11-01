<div class="fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center" id="loading-screen">
    <div class="text-center">
        <div class="w-16 h-16 border-4 border-teal-200 rounded-full animate-spin border-t-teal-600"></div>
        <p class="mt-4 text-lg font-medium text-gray-700">{{ __('app.loading') }}</p>
        <p class="text-sm text-gray-500">{{ __('app.please_wait') }}</p>
    </div>
</div>

<script>
    // Hide loading screen when page is fully loaded
    window.addEventListener('load', function () {
        const loadingScreen = document.getElementById('loading-screen');
        if (loadingScreen) {
            loadingScreen.style.opacity = '0';
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 300);
        }
    });

    // Show loading screen on form submissions
    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function () {
                const loadingScreen = document.getElementById('loading-screen');
                if (loadingScreen) {
                    loadingScreen.style.display = 'flex';
                    loadingScreen.style.opacity = '1';
                }
            });
        });
    });
</script>
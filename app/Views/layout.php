<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeetingKU</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <style>
        /* Remove underlines from all links */
        a {
            text-decoration: none !important;
        }
        /* Keep underline on hover for better UX */
        a:hover {
            text-decoration: none !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold text-indigo-600">Meetingku</span>
                    </div>
                    <!-- Mobile burger button -->
                    <button id="mobileMenuButton" type="button" class="ml-3 inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:hidden" aria-controls="mobileMenu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars"></i>
                    </button>

                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="<?= base_url('/') ?>" 
                               class="<?= current_url() == base_url('/') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Calendar
                            </a>
                            <a href="<?= base_url('/upcoming') ?>" 
                               class="<?= current_url() == base_url('/upcoming') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Meeting
                            </a>
                            <?php if (session()->get('is_admin')): ?>
                                <a href="<?= base_url('meeting/all') ?>" 
                                   class="<?= current_url() == base_url('meeting/all') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Semua Kegiatan
                                </a>
                                <a href="<?= base_url('pegawai') ?>" 
                                   class="<?= current_url() == base_url('pegawai') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Pegawai
                                </a>
                                <a href="<?= base_url('ruangan') ?>" 
                                   class="<?= current_url() == base_url('ruangan') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Ruangan
                                </a>
                            <?php endif; ?>
                        </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <?php if (session()->get('logged_in')): ?>
                        <div class="ml-3 relative">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-700">
                                    <?= session()->get('nama') ?>
                                    <?php if (session()->get('is_admin')): ?>
                                        <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            Admin
                                        </span>
                                    <?php endif; ?>
                                </span>
                                <a href="<?= base_url('auth/logout') ?>" 
                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-sign-out-alt mr-1.5"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="ml-3 relative">
                            <div class="flex items-center space-x-4">
                                <a href="<?= base_url('auth/login') ?>" 
                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-user mr-1.5"></i> Login
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile menu -->
    <div id="mobileMenu" class="sm:hidden hidden border-t border-gray-200 bg-white">
        <div class="pt-2 pb-3 space-y-1">
            <a href="<?= base_url('/') ?>" class="block pl-3 pr-4 py-2 text-base font-medium <?= current_url() == base_url('/') ? 'text-indigo-700' : 'text-gray-700 hover:text-gray-900' ?>">Calendar</a>
            <a href="<?= base_url('/upcoming') ?>" class="block pl-3 pr-4 py-2 text-base font-medium <?= current_url() == base_url('/upcoming') ? 'text-indigo-700' : 'text-gray-700 hover:text-gray-900' ?>">Meeting</a>
            <?php if (session()->get('is_admin')): ?>
                <a href="<?= base_url('pegawai') ?>" class="block pl-3 pr-4 py-2 text-base font-medium <?= current_url() == base_url('pegawai') ? 'text-indigo-700' : 'text-gray-700 hover:text-gray-900' ?>">Pegawai</a>
                <a href="<?= base_url('ruangan') ?>" class="block pl-3 pr-4 py-2 text-base font-medium <?= current_url() == base_url('ruangan') ? 'text-indigo-700' : 'text-gray-700 hover:text-gray-900' ?>">Ruangan</a>
                <a href="<?= base_url('meeting/all') ?>" class="block pl-3 pr-4 py-2 text-base font-medium <?= current_url() == base_url('meeting/all') ? 'text-indigo-700' : 'text-gray-700 hover:text-gray-900' ?>">Semua Kegiatan</a>
            <?php endif; ?>
            <?php if (session()->get('logged_in')): ?>
                <a href="<?= base_url('auth/logout') ?>" class="block pl-3 pr-4 py-2 text-base font-medium text-red-600 hover:text-red-700">Logout</a>
            <?php else: ?>
                <a href="<?= base_url('auth/login') ?>" class="block pl-3 pr-4 py-2 text-base font-medium text-indigo-600 hover:text-indigo-700">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?= $this->renderSection('content') ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function(){
        var btn = document.getElementById('mobileMenuButton');
        var menu = document.getElementById('mobileMenu');
        if (btn && menu) {
            btn.addEventListener('click', function(){
                var isHidden = menu.classList.contains('hidden');
                btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                if (isHidden) menu.classList.remove('hidden'); else menu.classList.add('hidden');
            });
        }
    })();
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>

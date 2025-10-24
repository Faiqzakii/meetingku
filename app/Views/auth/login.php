<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Meetingku</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Decorative Element -->
            <div class="absolute top-0 left-0 w-full h-2 gradient-bg"></div>
            
            <div class="bg-white rounded-xl shadow-lg p-8 animate-fadeIn">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-indigo-800 mb-2">
                        Meetingku
                    </h2>
                    <p class="text-sm text-gray-500">
                        Silakan login untuk melanjutkan
                    </p>
                </div>

                <form class="mt-8 space-y-6" action="<?= base_url('auth/login') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="space-y-4">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input id="username" 
                                       name="username" 
                                       type="text" 
                                       required 
                                       class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-200" 
                                       placeholder="Enter your username"
                                       value="<?= old('username') ?>">
                            </div>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="password" 
                                       name="password" 
                                       type="password" 
                                       required 
                                       class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors duration-200" 
                                       placeholder="Enter your password">
                            </div>
                        </div>
                    </div>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="rounded-lg bg-red-50 p-4 animate-fadeIn">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <?php 
                                        $error = session()->getFlashdata('error');
                                        if (is_string($error)) {
                                            echo "<p class='text-sm font-medium text-red-800'>" . esc($error) . "</p>";
                                        } else if (is_array($error)) {
                                            echo "<ul class='text-sm font-medium text-red-800 list-disc list-inside'>";
                                            foreach ($error as $err) {
                                                echo "<li>" . esc($err) . "</li>";
                                            }
                                            echo "</ul>";
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="rounded-lg bg-green-50 p-4 animate-fadeIn">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-400 text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">
                                        <?= session()->getFlashdata('success') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div>
                        <button type="submit" 
                                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white gradient-bg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-sign-in-alt text-indigo-200 group-hover:text-white transition-colors duration-200"></i>
                            </span>
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

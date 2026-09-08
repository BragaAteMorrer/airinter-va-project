<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
        name='viewport' />

    <title><?php echo $__env->yieldContent('title'); ?> - <?php echo e(config('app.name')); ?></title>
    <script>
        // Check for saved user preference, if any, on initial load
        (function() {
            if (localStorage.getItem('theme') === 'dark' || ((!localStorage.getItem('theme') || localStorage.getItem(
                    'theme') === 'system') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-bs-theme', "dark")
            }
        })();
    </script>

    
    <meta name="base-url" content="<?php echo url(''); ?>">
    <meta name="api-key" content="<?php echo Auth::check() ? Auth::user()->api_key : ''; ?>">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    

    <link rel="shortcut icon" type="image/png" href="<?php echo e(public_asset('/assets/img/favicon.png')); ?>" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cookieconsent/3.1.1/cookieconsent.min.css"
        integrity="sha512-LQ97camar/lOliT/MqjcQs5kWgy6Qz/cCRzzRzUCfv0fotsCTC9ZHXaPQmJV8Xu/PVALfJZ7BDezl5lW3/qBxg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
    <link href="<?php echo e(public_asset('/assets/vendor/tomselect/tom-select.bootstrap5.css')); ?>" rel="stylesheet">

    
    
    <?php echo $__env->yieldContent('css'); ?>
    <?php echo $__env->yieldContent('scripts_head'); ?>
    

    <style>
        .nav-link:hover {
            color: orange !important;
        }

        :root {
            --bs-primary: #067ec1 !important;


        }

        [data-bs-theme=light] {
            --bs-primary: #067ec1 !important;
        }

        .bg-primary {
            background-color: #067ec1 !important;
        }

        .btn-primary {
            background-color: #067ec1 !important;
            border-color: #067ec1 !important;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="wrapper d-flex flex-column min-vh-100">
        <?php echo $__env->make('nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="body container flex-grow-1 pt-4">
            
            <?php echo $__env->make('flash.message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->yieldContent('content'); ?>
            

        </div>
        <footer class="py-3 mt-4 border-top">
            <div class="container d-flex flex-wrap justify-content-between align-items-center">

                <div class="col-md-4 d-flex align-items-center">
                    <span class="mb-3 mb-md-0 text-body-secondary">Copyright <?php echo e(date('Y')); ?>

                        <?php echo e(config('app.name')); ?></span>
                </div>
                <div class="col-md-4 d-flex align-items-center justify-content-end">
                    <span class="mb-3 mb-md-0 text-body-secondary text-end">Powered by <a href="https://www.phpvms.net"
                            target="_blank">phpVMS</a></span>
                </div>
            </div>
        </footer>
    </div>

    
    <?php echo $__env->make('external_redirect_modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>

    <script>
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
    </script>

    
    <script src="<?php echo e(public_mix('/assets/global/js/vendor.js')); ?>"></script>
    <script src="<?php echo e(public_mix('/assets/frontend/js/vendor.js')); ?>"></script>
    <script src="<?php echo e(public_mix('/assets/frontend/js/app.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>

    
    <?php echo $__env->make('scripts.bs_theme', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <script>
        window.addEventListener("load", function() {
            window.cookieconsent.initialise({
                palette: {
                    popup: {
                        background: "#edeff5",
                        text: "#838391"
                    },
                    button: {
                        "background": "#067ec1"
                    }
                },
                position: "top",
            })
        });
    </script>
    

    
    <?php
        $gtag = setting('general.google_analytics_id');
    ?>
    <?php if($gtag): ?>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo e($gtag); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());

            gtag('config', '<?php echo e($gtag); ?>');
        </script>
    <?php endif; ?>
    

</body>

</html>
<?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/seven/app.blade.php ENDPATH**/ ?>
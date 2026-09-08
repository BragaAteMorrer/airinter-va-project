<?php

/*
|--------------------------------------------------------------------------
| Load The Cached Routes
|--------------------------------------------------------------------------
|
| Here we will decode and unserialize the RouteCollection instance that
| holds all of the route information for an application. This allows
| us to instantaneously load the entire route map into the router.
|
*/

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/arrilot/load-widget' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::aZ4vNsVpvTveLE8L',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/log-viewer/api/hosts' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.hosts',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/log-viewer/api/folders' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.folders',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/log-viewer/api/files' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.files',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/log-viewer/api/clear-cache-all' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.files.clear-cache-all',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/log-viewer/api/delete-multiple-files' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.files.delete-multiple-files',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/log-viewer/api/logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.logs',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/health-check' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.healthCheck',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/execute-solution' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.executeSolution',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/update-config' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.updateConfig',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/broadcasting/auth' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::AcqNO9xq9uVIMyzT',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.dashboard.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.dashboard.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.dashboard.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/downloads' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.downloads.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/flights/bids' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.flights.bids',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/flights/search' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.flights.search',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/flights' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.flights.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.flights.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/flights/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.flights.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/pireps/fares' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/pireps' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pireps.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pireps.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/pireps/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pireps.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/profile/acars' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.profile.acars',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/profile/regen_apikey' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.profile.regen_apikey',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/profile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.profile.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.profile.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/profile/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.profile.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/simbrief/generate' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.simbrief.generate',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/simbrief/apicode' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.simbrief.api_code',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/simbrief/check_ofp' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.simbrief.check_ofp',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/simbrief/update_ofp' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.simbrief.update_ofp',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.home',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/pilots' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pilots.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/livemap' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.livemap.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/credits' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.credits',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'auth.logout',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::V3U4Xytvrm3BR2Jx',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/register' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'register',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::OFz90BFAVURRFJLV',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/password/reset' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.request',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'password.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/password/email' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.email',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/email/verify' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'verification.notice',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/email/resend' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'verification.resend',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/airlines' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airlines.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airlines.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/airlines/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airlines.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/roles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/roles/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/airports/export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airports.export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/airports/fuel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'HEAD' => 3,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/airports/import' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airports.import',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/airports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airports.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airports.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/airports/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airports.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/awards' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/awards/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/aircraft/export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.aircraft.export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/aircraft/import' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.aircraft.import',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/aircraft' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.aircraft.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.aircraft.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/aircraft/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.aircraft.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/expenses/export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.expenses.export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/expenses/import' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.expenses.import',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/expenses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.expenses.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.expenses.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/expenses/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.expenses.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/fares/export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fares.export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/fares/import' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fares.import',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/fares' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fares.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fares.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/fares/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fares.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/files' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.files.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/finances' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.finances.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.finances.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/finances/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.finances.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/flights/export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flights.export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/flights/import' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flights.import',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/flights' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flights.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flights.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/flights/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flights.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/flightfields' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flightfields.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flightfields.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/flightfields/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flightfields.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/userfields' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.userfields.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.userfields.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/userfields/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.userfields.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pireps/fares' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::KxuPs1WOGMFn4RQQ',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pireps/pending' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::bffWcGNR7NMVfZhs',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pireps' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pireps.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pireps.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pireps/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pireps.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pirepfields' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pirepfields.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pirepfields.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pirepfields/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pirepfields.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pages' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pages.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pages.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pages/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pages.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/ranks' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ranks.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ranks.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/ranks/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ranks.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/settings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::fGhkCpvK4XCwu1QV',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
            'PUT' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/typeratings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.typeratings.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.typeratings.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/typeratings/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.typeratings.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/airframes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airframes.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airframes.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/airframes/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airframes.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sbupdate' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airframes.sbupdate',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maintenance' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maintenance.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maintenance/cache' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maintenance.cache',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maintenance/queue' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maintenance.queue',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maintenance/update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maintenance.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maintenance/forcecheck' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maintenance.forcecheck',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maintenance/reseed' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maintenance.reseed',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maintenance/cron_enable' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maintenance.cron_enable',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maintenance/cron_disable' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maintenance.cron_disable',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/subfleets/export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.subfleets.export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/subfleets/import' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.subfleets.import',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/subfleets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.subfleets.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.subfleets.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/subfleets/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.subfleets.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/invites' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invites.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invites.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/invites/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invites.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::UFwqohDvWHGMQz87',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::h7dMrQ1FlC6Zxybh',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dashboard/news' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.dashboard.news',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'PATCH' => 1,
            'POST' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/activities' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.activities.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/modules' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.modules.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/modules/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.modules.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.modules.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/modules/enable' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.modules.enable',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/acars' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::rQeyKbjtf2eSTaBs',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/acars/geojson' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::t8FBKA8c2Rbn1UA2',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/airports/hubs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::M7mYar6kiHZElhV3',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/airports/search' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::kK6eilrReeqA7Wtj',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/news' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::xRYZaLFyVUuPQ2kK',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/status' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::7syfxuNGe07YtBpu',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/version' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::F3ys03MzFYGrD0Av',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/airlines' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::mtvMKSRcX4TiAd7b',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/airports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::b08DWrEl8lbNszEv',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/fleet' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::7QMrSrs6WbDc4VZu',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/subfleet' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::Q1gQPTuhHBmRJVzj',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/flights' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::1Mpyg3HEr2a4ou4K',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/flights/search' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::7HVfbkKCm63lpLAH',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/pireps' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::VdY0hpM0qLyNV1R3',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/user' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::oFTsrsmcIjr5hpTd',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/user/fleet' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::l3FfDiAo8PB1yRRi',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/user/pireps' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::dIP0YG9NX0ulwO78',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/bids' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::A6Fk8i4XjSl0SEqU',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/user/bids' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::DuzQ7Bywm8Uk1n8X',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::acgrNjfaa6wGkJ5B',
          ),
          1 => NULL,
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::T6sNVynp4HdvxdFN',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        3 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::u6Co68ueLIGv9z3h',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/users/me' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::BgTlv6d0e6JkvqOx',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'installer.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install/dbtest' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'installer.dbtest',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install/step1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'installer.step1',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'installer.step1post',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install/step2' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'installer.step2',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install/envsetup' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'installer.envsetup',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install/dbsetup' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'installer.dbsetup',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install/step3' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'installer.step3',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install/usersetup' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'installer.usersetup',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/install/complete' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'installer.complete',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'update.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/update/step1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'update.step1',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'update.step1post',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/update/run-migrations' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'update.run_migrations',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/update/complete' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'update.complete',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dairports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DAirports.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dairports/update_all' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DAirports.update_all',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/disposableairports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DAirports.module_index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dsettings_update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.settings_update',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dairports/fix_uz' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DAirports.fix_uzbekistan',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dairports/cleanup' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DAirports.cleanup_airports',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dairlines' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.airlines',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dawards' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.awards',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dhubs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.hubs',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dnews' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.news',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dranks' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.ranks',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/droster' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.roster',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dlivewx' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.livewx',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dpireps' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.pireps',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dscenery' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.scenery',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dflights' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.scenery.flights',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dscenery/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.scenery.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dscenery/delete' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.scenery.delete',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dstable' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.stable',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dstats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/djumpseat' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.jumpseat',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dtransferac' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.transferac',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dfleet' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.fleet',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dreports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.reports',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dstatistics' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.statistics',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dvatsim' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.vatsim',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/divao' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.ivao',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/daudit_export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.audit.export',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dp_roster' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.dp_roster',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dp_stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.dp_stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dp_page' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.dp_page',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dp_pireps' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.dp_pireps',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dstable/new' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dbapi/events' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.generated::G6Z1H04ib8xM1I1J',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dbapi/modules' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.generated::FrTb8qprrTyYm1QM',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dbapi/news' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.generated::we4ZH5p3jkamnvzA',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dbapi/pireps' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.generated::bz6xEKOOjXc2EpGq',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dbapi/roster' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.generated::kt1sCxP8HNJ08NGm',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dbapi/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.generated::cIq4Mgq5jLaHfUW0',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dbasic' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.admin',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dcheck' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.health_check',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dpark_aircraft' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.park_aircraft',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dspecs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.specs',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dspecs_store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.specs_store',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dtech' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.tech',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dtech_store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.tech_store',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/drunway' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.runway',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/drunway_store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.runway_store',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dstable/update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.stable_update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dmanual_award' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.manual_award',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dmanual_payment' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.manual_payment',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dassignments' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.assignments',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dfreeflight' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.freeflight',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dfreeflight_store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.freeflight_store',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dmaintenance' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.maintenance',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dmarket' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.market',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dmissions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.missions',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dmissions/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.missions.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dnotams' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.notams',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dopsmanual' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.ops_manual',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dlandingrates' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.landing_rates',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dtours' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.tours',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/daboutus' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.about_us',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/drulesandregs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.rules_regs',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dsapi/assignments' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dsapi/modules' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.generated::ywCl7bNJngURzD4i',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dsapi/tours' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.generated::DvHteDDNULDlOYMl',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dspecial' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.admin',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dsettings_store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.save_settings',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dassignments_manual' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.assignments_manual',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dmarket_admin' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.market_admin',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dmarket_store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.market_store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dmaint_admin' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.maint_admin',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dmaint_finish' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.maint_finish',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dnotam_admin' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.notam_admin',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dnotam_store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.notam_store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dtour_admin' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.tour_admin',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dtour_store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.tour_store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dtour_legactions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.tour_legactions',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/passport' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/passport' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.passport.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/passport' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.passport.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/passport/hello' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.passport.generated::2zudDgnfDyauhuDL',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sptheme' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sptheme.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sptheme' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.sptheme.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sptheme/sptheme' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.sptheme.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sptheme/sptheme/updateSettings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.sptheme.storeSettings',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sptheme/sptheme/updateCrewtest' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.sptheme.storeCrewtest',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sptheme/sptheme/resetSettings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.sptheme.resetSettings',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/sptheme' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.sptheme.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/sptheme/hello' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.sptheme.generated::FQuIeUrxyNLwQ7h5',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sptransfer/hub' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sptransfer.hub.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'sptransfer.hub.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sptransfer/airline' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sptransfer.airline.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'sptransfer.airline.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sptransfer' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.sptransfer.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sptransfer/update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.sptransfer.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sptransfer/update_airline' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.sptransfer.update_airline',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sptransfer/storeSettings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.sptransfer.storeSettings',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sample' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sample.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sample' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sample.generated::QrQVG5GJbl3Xb6Cd',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/sample/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sample.generated::tKrbZBNLMoT9QMTE',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/sample' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sample.generated::H6O1HRHHa5hk1axr',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/sample/hello' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sample.generated::ApLqx5X9Ri4apaQ5',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/vmsacars' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'vmsacars.admin.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/vmsacars/config' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'vmsacars.admin.config',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/vmsacars/rules' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'vmsacars.admin.rules',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/vmsacars/broadcast' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'vmsacars.admin.broadcast',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/vmsacars/config' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'vmsacars.api.vmsacars.api.config',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/vmsacars/search' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'vmsacars.api.vmsacars.api.search',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/vmsacars/rules' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'vmsacars.api.rules',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/a(?|dmin/(?|log\\-viewer(?|/api/f(?|olders/([^/]++)(?|/(?|download(?|/request(*:84)|(*:91))|clear\\-cache(*:111))|(*:120))|iles/([^/]++)(?|/(?|download(?|/request(*:168)|(*:176))|clear\\-cache(*:197))|(*:206)))|(?:/((?:.*)))?(*:230))|a(?|ir(?|lines/([^/]++)(?|(*:265)|/edit(*:278)|(*:286))|ports/([^/]++)(?|/e(?|xpenses(*:324)|dit(*:335))|(*:344))|craft/(?|([^/]++)(?|/e(?|xpenses(*:385)|dit(*:396))|(*:405))|trashbin(*:422))|frames/([^/]++)(?|(*:449)|/edit(*:462)|(*:470)))|wards/([^/]++)(?|(*:497)|/edit(*:510)|(*:518))|ctivities/([^/]++)(*:545))|r(?|oles/([^/]++)(?|(*:574)|/edit(*:587)|(*:595))|anks/([^/]++)(?|(*:620)|/(?|edit(*:636)|subfleets(*:653))|(*:662)))|expenses/([^/]++)(?|(*:692)|/edit(*:705)|(*:713))|f(?|ares/(?|([^/]++)(?|(*:745)|/edit(*:758)|(*:766))|trashbin(*:783))|i(?|les/([^/]++)(*:808)|nances/([^/]++)(?|(*:834)|/edit(*:847)|(*:855)))|light(?|s/([^/]++)(?|/(?|f(?|ares(*:898)|ields(*:911))|subfleets(*:929)|edit(*:941))|(*:950))|fields/([^/]++)(?|(*:977)|/edit(*:990)|(*:998))))|user(?|fields/([^/]++)(?|(*:1034)|/edit(*:1048)|(*:1057))|s/([^/]++)(?|/(?|award/([^/]++)(*:1098)|re(?|gen_apikey(*:1122)|quest_email_verification(*:1155))|verify_email(*:1177)|edit(*:1190)|typeratings(*:1210))|(*:1220)))|p(?|irep(?|s/([^/]++)(?|(*:1255)|/(?|edit(*:1272)|comments(*:1289)|status(*:1304))|(*:1314))|fields/([^/]++)(?|(*:1342)|/edit(*:1356)|(*:1365)))|ages/([^/]++)(?|(*:1392)|/edit(*:1406)|(*:1415)))|typeratings/([^/]++)(?|(*:1449)|/(?|edit(*:1466)|subfleets(*:1484)|users(*:1498))|(*:1508))|subfleets/(?|([^/]++)(?|/(?|e(?|xpenses(*:1557)|dit(*:1569))|fares(*:1584)|ranks(*:1598)|typeratings(*:1618))|(*:1628))|trashbin(*:1646))|invites/([^/]++)(*:1672)|modules/([^/]++)(?|/edit(*:1705)|(*:1714))|d(?|airports/(?|destroy/([^/]++)(*:1756)|restore/([^/]++)(*:1781)|update/([^/]++)(*:1805))|tours/remove/([^/]++)(*:1836)))|irports/([^/]++)(*:1863)|pi/(?|pireps/(?|([^/]++)(?|(*:1899)|/acars/geojson(*:1922)|(*:1931))|prefile(*:1948)|([^/]++)(?|(*:1968)|/(?|update(?|(*:1990))|fi(?|le(*:2007)|elds(?|(*:2023))|nances(?|(*:2042)|/recalculate(*:2063)))|c(?|omments(?|(*:2088))|ancel(?|(*:2106)))|route(?|(*:2125))|acars/(?|position(?|(*:2155)|s(*:2165))|events(*:2181)|logs(*:2194)))))|cron/([^/]++)(*:2220)|air(?|lines/([^/]++)(*:2249)|ports/([^/]++)(?|(*:2275)|/(?|lookup(*:2294)|distance/([^/]++)(*:2320))))|fl(?|eet/aircraft/([^/]++)(*:2358)|ights/([^/]++)(?|(*:2384)|/(?|briefing(*:2405)|route(*:2419)|aircraft(*:2436))))|subfleet/aircraft/([^/]++)(*:2474)|bids/([^/]++)(*:2496)|user(?|/bids/([^/]++)(*:2526)|s/([^/]++)(?|(*:2548)|/(?|fleet(*:2566)|pireps(*:2581)|bids(?|(*:2597)))))))|/d(?|a(?|shboard/([^/]++)(?|(*:2640)|/edit(*:2654)|(*:2663))|ir(?|line(?|s/([^/]++)(*:2695)|/([^/]++)(*:2713))|craft/([^/]++)(*:2737)))|ownloads/([^/]++)(*:2765)|fleet/([^/]++)(*:2788)|hubs/([^/]++)(*:2810)|market/(?|([^/]++)(*:2837)|buy(*:2849))|tours/([^/]++)(*:2873))|/flights/([^/]++)(?|(*:2903)|/edit(*:2917)|(*:2926))|/p(?|i(?|reps/([^/]++)(?|/(?|submit(*:2971)|edit(*:2984))|(*:2994)|(*:3003))|lots/([^/]++)(*:3026))|rofile/([^/]++)(?|(*:3054)|/edit(*:3068)|(*:3077)|(*:3086))|a(?|ge/([^/]++)(*:3111)|ss(?|word/reset/([^/]++)(*:3144)|port/(?|compare/([0-9]+)(*:3177)|flights/([a-zA-Z]+)(*:3205)))))|/simbrief/([^/]++)(?|(*:3239)|/(?|prefile(*:3259)|cancel(*:3274)|generate_new(*:3295)))|/r/([^/]++)(*:3317)|/users/([^/]++)(*:3341)|/lang/([^/]++)(*:3364)|/oauth/([^/]++)/(?|redirect(*:3400)|callback(*:3417)|logout(*:3432))|/email/verify/([^/]++)/([^/]++)(*:3473))/?$}sDu',
    ),
    3 => 
    array (
      84 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.folders.request-download',
          ),
          1 => 
          array (
            0 => 'folderIdentifier',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      91 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.folders.download',
          ),
          1 => 
          array (
            0 => 'folderIdentifier',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      111 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.folders.clear-cache',
          ),
          1 => 
          array (
            0 => 'folderIdentifier',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      120 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.folders.delete',
          ),
          1 => 
          array (
            0 => 'folderIdentifier',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      168 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.files.request-download',
          ),
          1 => 
          array (
            0 => 'fileIdentifier',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      176 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.files.download',
          ),
          1 => 
          array (
            0 => 'fileIdentifier',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      197 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.files.clear-cache',
          ),
          1 => 
          array (
            0 => 'fileIdentifier',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      206 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.files.delete',
          ),
          1 => 
          array (
            0 => 'fileIdentifier',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      230 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'log-viewer.index',
            'view' => NULL,
          ),
          1 => 
          array (
            0 => 'view',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      265 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airlines.show',
          ),
          1 => 
          array (
            0 => 'airline',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      278 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airlines.edit',
          ),
          1 => 
          array (
            0 => 'airline',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      286 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airlines.update',
          ),
          1 => 
          array (
            0 => 'airline',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airlines.destroy',
          ),
          1 => 
          array (
            0 => 'airline',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      324 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::0JXVVZma8UwISxzX',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      335 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airports.edit',
          ),
          1 => 
          array (
            0 => 'airport',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      344 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airports.show',
          ),
          1 => 
          array (
            0 => 'airport',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airports.update',
          ),
          1 => 
          array (
            0 => 'airport',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airports.destroy',
          ),
          1 => 
          array (
            0 => 'airport',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      385 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::5yU6ygvDB8B7J4jC',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      396 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.aircraft.edit',
          ),
          1 => 
          array (
            0 => 'aircraft',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      405 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.aircraft.show',
          ),
          1 => 
          array (
            0 => 'aircraft',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.aircraft.update',
          ),
          1 => 
          array (
            0 => 'aircraft',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.aircraft.destroy',
          ),
          1 => 
          array (
            0 => 'aircraft',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      422 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.aircraft.trashbin',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      449 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airframes.show',
          ),
          1 => 
          array (
            0 => 'airframe',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      462 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airframes.edit',
          ),
          1 => 
          array (
            0 => 'airframe',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      470 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airframes.update',
          ),
          1 => 
          array (
            0 => 'airframe',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.airframes.destroy',
          ),
          1 => 
          array (
            0 => 'airframe',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      497 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.show',
          ),
          1 => 
          array (
            0 => 'award',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      510 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.edit',
          ),
          1 => 
          array (
            0 => 'award',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      518 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.update',
          ),
          1 => 
          array (
            0 => 'award',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.destroy',
          ),
          1 => 
          array (
            0 => 'award',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      545 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.activities.show',
          ),
          1 => 
          array (
            0 => 'activity',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      574 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.show',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      587 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.edit',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      595 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.update',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.destroy',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      620 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ranks.show',
          ),
          1 => 
          array (
            0 => 'rank',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      636 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ranks.edit',
          ),
          1 => 
          array (
            0 => 'rank',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      653 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::ng8rtoiI2utJOhEN',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      662 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ranks.update',
          ),
          1 => 
          array (
            0 => 'rank',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ranks.destroy',
          ),
          1 => 
          array (
            0 => 'rank',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      692 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.expenses.show',
          ),
          1 => 
          array (
            0 => 'expense',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      705 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.expenses.edit',
          ),
          1 => 
          array (
            0 => 'expense',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      713 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.expenses.update',
          ),
          1 => 
          array (
            0 => 'expense',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.expenses.destroy',
          ),
          1 => 
          array (
            0 => 'expense',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      745 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fares.show',
          ),
          1 => 
          array (
            0 => 'fare',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      758 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fares.edit',
          ),
          1 => 
          array (
            0 => 'fare',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      766 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fares.update',
          ),
          1 => 
          array (
            0 => 'fare',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fares.destroy',
          ),
          1 => 
          array (
            0 => 'fare',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      783 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fares.trashbin',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      808 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.files.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      834 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.finances.show',
          ),
          1 => 
          array (
            0 => 'finance',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      847 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.finances.edit',
          ),
          1 => 
          array (
            0 => 'finance',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      855 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.finances.update',
          ),
          1 => 
          array (
            0 => 'finance',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.finances.destroy',
          ),
          1 => 
          array (
            0 => 'finance',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      898 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::teqbimvQgDZFjMv4',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      911 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::4v2lsffM12tf2aiV',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      929 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::jY0FtpAFpVxH27DW',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      941 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flights.edit',
          ),
          1 => 
          array (
            0 => 'flight',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      950 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flights.show',
          ),
          1 => 
          array (
            0 => 'flight',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flights.update',
          ),
          1 => 
          array (
            0 => 'flight',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flights.destroy',
          ),
          1 => 
          array (
            0 => 'flight',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      977 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flightfields.show',
          ),
          1 => 
          array (
            0 => 'flightfield',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      990 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flightfields.edit',
          ),
          1 => 
          array (
            0 => 'flightfield',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      998 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flightfields.update',
          ),
          1 => 
          array (
            0 => 'flightfield',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.flightfields.destroy',
          ),
          1 => 
          array (
            0 => 'flightfield',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1034 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.userfields.show',
          ),
          1 => 
          array (
            0 => 'userfield',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1048 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.userfields.edit',
          ),
          1 => 
          array (
            0 => 'userfield',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1057 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.userfields.update',
          ),
          1 => 
          array (
            0 => 'userfield',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.userfields.destroy',
          ),
          1 => 
          array (
            0 => 'userfield',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1098 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.destroy_user_award',
          ),
          1 => 
          array (
            0 => 'id',
            1 => 'award_id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1122 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.regen_apikey',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1155 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.request_email_verification',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1177 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.verify_email',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1190 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.edit',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1210 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::KcviFz9i7w34FNUt',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1220 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.show',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1255 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pireps.show',
          ),
          1 => 
          array (
            0 => 'pirep',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1272 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pireps.edit',
          ),
          1 => 
          array (
            0 => 'pirep',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1289 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::UUriHB2JvD0lGbNC',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'DELETE' => 2,
            'HEAD' => 3,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1304 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pirep.status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
            'PUT' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1314 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pireps.update',
          ),
          1 => 
          array (
            0 => 'pirep',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pireps.destroy',
          ),
          1 => 
          array (
            0 => 'pirep',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1342 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pirepfields.show',
          ),
          1 => 
          array (
            0 => 'pirepfield',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1356 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pirepfields.edit',
          ),
          1 => 
          array (
            0 => 'pirepfield',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1365 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pirepfields.update',
          ),
          1 => 
          array (
            0 => 'pirepfield',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pirepfields.destroy',
          ),
          1 => 
          array (
            0 => 'pirepfield',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1392 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pages.show',
          ),
          1 => 
          array (
            0 => 'page',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1406 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pages.edit',
          ),
          1 => 
          array (
            0 => 'page',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1415 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pages.update',
          ),
          1 => 
          array (
            0 => 'page',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pages.destroy',
          ),
          1 => 
          array (
            0 => 'page',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1449 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.typeratings.show',
          ),
          1 => 
          array (
            0 => 'typerating',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1466 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.typeratings.edit',
          ),
          1 => 
          array (
            0 => 'typerating',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1484 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::9L1QmtKnZdrHJ7D6',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1498 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::4HCg2D4NPEVLjRU2',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1508 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.typeratings.update',
          ),
          1 => 
          array (
            0 => 'typerating',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.typeratings.destroy',
          ),
          1 => 
          array (
            0 => 'typerating',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1557 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::EbQG37mG5XoNn2C7',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1569 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.subfleets.edit',
          ),
          1 => 
          array (
            0 => 'subfleet',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1584 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::Fi9Py65u8BmAWjbt',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1598 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::WkfDz566Ccr9fnB8',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1618 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::H62BiHW89qnzEcbw',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1628 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.subfleets.show',
          ),
          1 => 
          array (
            0 => 'subfleet',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.subfleets.update',
          ),
          1 => 
          array (
            0 => 'subfleet',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.subfleets.destroy',
          ),
          1 => 
          array (
            0 => 'subfleet',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1646 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.subfleets.trashbin',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1672 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.invites.destroy',
          ),
          1 => 
          array (
            0 => 'invite',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1705 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.modules.edit',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1714 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.modules.update',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.modules.destroy',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1756 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DAirports.destroy_airport',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1781 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DAirports.restore_airport',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1805 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DAirports.update_airport',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1836 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.tour_remove',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1863 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.airports.show',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1899 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::vbqmWAVuOgedhSOw',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1922 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::koLXS6B3k2oXQ0OR',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1931 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::FEVcFqjyWPiOVsLR',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1948 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::vAiR46HKMSC3Zq2l',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1968 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::OEU6esQ9aNILxEL1',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::rADkFxHbUQpRNrXN',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1990 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::sRA0z5GAa1z9tg5t',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::nlOA10txz5TpxbQE',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2007 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::9RMFJjOELRze4Ozu',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2023 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::ZAQNBy3gs3z9rZUi',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::6QuvIiIe6LVafqxG',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2042 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::787FAq2lYCZ0rji3',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2063 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::yo7Hx1B5ISheVa2v',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2088 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::RObCxoA9rgY4gwsF',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::ZpGcJY4OT1G9h4Ek',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2106 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::NOzkZB71q4F2fCzi',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::ZmbS76OKCNBJuZ3N',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2125 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::yBBWHOQIA3xQ8kQz',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::htoRZdH2ZBylsNl8',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::jDdB8aa7YAErKjho',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2155 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::8RplGoNOL8TME0EM',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::eLQTE1YBZzbyroef',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2165 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::cjGa3kB3WmeJOIgz',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2181 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::JHwp3bCkaooIfVN3',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2194 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::eScJjezwIMhlyv7O',
          ),
          1 => 
          array (
            0 => 'pirep_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2220 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.maintenance.cron',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2249 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::0wUYxJwDdVClYqHm',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2275 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::VYGxjQmz5sFeMgnT',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2294 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::lG4uyIUXgoOiwQIQ',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2320 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::ue3XjoRiUArIWEWX',
          ),
          1 => 
          array (
            0 => 'id',
            1 => 'to',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2358 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::5Q7oiau4nLhThQe4',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2384 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::WoVjFC8Sonzd1QeE',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2405 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.flights.briefing',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2419 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::YC0qDYTHhj1QCGa0',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2436 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::QwZLHjhrKP7SyNes',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2474 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::0006g8ovarlNoGkd',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2496 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::kLvJX9PJBU1TDX6L',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2526 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::yK173lSmEMgLbZhL',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2548 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::atfbv6njt4qdrgb2',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2566 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::tesQU9NpFn6Sgcyj',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2581 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::JPLrURvaYGc98ERL',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2597 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::sM5E1PNmJ3OU2XOr',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'api.generated::ZVQyuDXtZaNIv7LY',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2640 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.dashboard.show',
          ),
          1 => 
          array (
            0 => 'dashboard',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2654 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.dashboard.edit',
          ),
          1 => 
          array (
            0 => 'dashboard',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2663 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.dashboard.update',
          ),
          1 => 
          array (
            0 => 'dashboard',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.dashboard.destroy',
          ),
          1 => 
          array (
            0 => 'dashboard',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2695 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.airline',
          ),
          1 => 
          array (
            0 => 'icao',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2713 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.myairline',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2737 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.aircraft',
          ),
          1 => 
          array (
            0 => 'ac_reg',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2765 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.downloads.download',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2788 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.subfleet',
          ),
          1 => 
          array (
            0 => 'subfleet_type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2810 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DBasic.hub',
          ),
          1 => 
          array (
            0 => 'icao',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2837 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.market.show',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2849 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.market.buy',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2873 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'DSpecial.tour',
          ),
          1 => 
          array (
            0 => 'code',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2903 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.flights.show',
          ),
          1 => 
          array (
            0 => 'flight',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2917 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.flights.edit',
          ),
          1 => 
          array (
            0 => 'flight',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2926 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.flights.update',
          ),
          1 => 
          array (
            0 => 'flight',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.flights.destroy',
          ),
          1 => 
          array (
            0 => 'flight',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2971 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pireps.submit',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2984 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pireps.edit',
          ),
          1 => 
          array (
            0 => 'pirep',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2994 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pireps.update',
          ),
          1 => 
          array (
            0 => 'pirep',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pireps.destroy',
          ),
          1 => 
          array (
            0 => 'pirep',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3003 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pireps.show',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3026 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pilots.show.public',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3054 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.profile.show',
          ),
          1 => 
          array (
            0 => 'profile',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3068 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.profile.edit',
          ),
          1 => 
          array (
            0 => 'profile',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3077 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.profile.update',
          ),
          1 => 
          array (
            0 => 'profile',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.profile.destroy',
          ),
          1 => 
          array (
            0 => 'profile',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3086 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.profile.show.public',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3111 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pages.show',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3144 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.reset',
          ),
          1 => 
          array (
            0 => 'token',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3177 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.compare',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3205 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.flights.country',
          ),
          1 => 
          array (
            0 => 'country',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3239 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.simbrief.briefing',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3259 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.simbrief.prefile',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3274 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.simbrief.cancel',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3295 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.simbrief.generate_new',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3317 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.pirep.show.public',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3341 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.users.show.public',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3364 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'frontend.lang.switch',
          ),
          1 => 
          array (
            0 => 'lang',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3400 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'oauth.redirect',
          ),
          1 => 
          array (
            0 => 'provider',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3417 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'oauth.callback',
          ),
          1 => 
          array (
            0 => 'provider',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3432 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'oauth.logout',
          ),
          1 => 
          array (
            0 => 'provider',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3473 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'verification.verify',
          ),
          1 => 
          array (
            0 => 'id',
            1 => 'hash',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'generated::aZ4vNsVpvTveLE8L' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'arrilot/load-widget',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Arrilot\\Widgets\\Controllers\\WidgetController@showWidget',
        'controller' => 'Arrilot\\Widgets\\Controllers\\WidgetController@showWidget',
        'namespace' => 'Arrilot\\Widgets\\Controllers',
        'prefix' => 'arrilot',
        'where' => 
        array (
        ),
        'as' => 'generated::aZ4vNsVpvTveLE8L',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.hosts' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/log-viewer/api/hosts',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\HostsController@index',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\HostsController@index',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.hosts',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.folders' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/log-viewer/api/folders',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Opcodes\\LogViewer\\Http\\Middleware\\ForwardRequestToHostMiddleware',
          3 => 'Opcodes\\LogViewer\\Http\\Middleware\\JsonResourceWithoutWrappingMiddleware',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FoldersController@index',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FoldersController@index',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.folders',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.folders.request-download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/log-viewer/api/folders/{folderIdentifier}/download/request',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Opcodes\\LogViewer\\Http\\Middleware\\ForwardRequestToHostMiddleware',
          3 => 'Opcodes\\LogViewer\\Http\\Middleware\\JsonResourceWithoutWrappingMiddleware',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FoldersController@requestDownload',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FoldersController@requestDownload',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.folders.request-download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.folders.clear-cache' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/log-viewer/api/folders/{folderIdentifier}/clear-cache',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Opcodes\\LogViewer\\Http\\Middleware\\ForwardRequestToHostMiddleware',
          3 => 'Opcodes\\LogViewer\\Http\\Middleware\\JsonResourceWithoutWrappingMiddleware',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FoldersController@clearCache',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FoldersController@clearCache',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.folders.clear-cache',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.folders.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/log-viewer/api/folders/{folderIdentifier}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Opcodes\\LogViewer\\Http\\Middleware\\ForwardRequestToHostMiddleware',
          3 => 'Opcodes\\LogViewer\\Http\\Middleware\\JsonResourceWithoutWrappingMiddleware',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FoldersController@delete',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FoldersController@delete',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.folders.delete',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.files' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/log-viewer/api/files',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Opcodes\\LogViewer\\Http\\Middleware\\ForwardRequestToHostMiddleware',
          3 => 'Opcodes\\LogViewer\\Http\\Middleware\\JsonResourceWithoutWrappingMiddleware',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@index',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@index',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.files',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.files.request-download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/log-viewer/api/files/{fileIdentifier}/download/request',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Opcodes\\LogViewer\\Http\\Middleware\\ForwardRequestToHostMiddleware',
          3 => 'Opcodes\\LogViewer\\Http\\Middleware\\JsonResourceWithoutWrappingMiddleware',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@requestDownload',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@requestDownload',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.files.request-download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.files.clear-cache' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/log-viewer/api/files/{fileIdentifier}/clear-cache',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Opcodes\\LogViewer\\Http\\Middleware\\ForwardRequestToHostMiddleware',
          3 => 'Opcodes\\LogViewer\\Http\\Middleware\\JsonResourceWithoutWrappingMiddleware',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@clearCache',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@clearCache',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.files.clear-cache',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.files.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/log-viewer/api/files/{fileIdentifier}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Opcodes\\LogViewer\\Http\\Middleware\\ForwardRequestToHostMiddleware',
          3 => 'Opcodes\\LogViewer\\Http\\Middleware\\JsonResourceWithoutWrappingMiddleware',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@delete',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@delete',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.files.delete',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.files.clear-cache-all' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/log-viewer/api/clear-cache-all',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Opcodes\\LogViewer\\Http\\Middleware\\ForwardRequestToHostMiddleware',
          3 => 'Opcodes\\LogViewer\\Http\\Middleware\\JsonResourceWithoutWrappingMiddleware',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@clearCacheAll',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@clearCacheAll',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.files.clear-cache-all',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.files.delete-multiple-files' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/log-viewer/api/delete-multiple-files',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Opcodes\\LogViewer\\Http\\Middleware\\ForwardRequestToHostMiddleware',
          3 => 'Opcodes\\LogViewer\\Http\\Middleware\\JsonResourceWithoutWrappingMiddleware',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@deleteMultipleFiles',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@deleteMultipleFiles',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.files.delete-multiple-files',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.logs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/log-viewer/api/logs',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Opcodes\\LogViewer\\Http\\Middleware\\ForwardRequestToHostMiddleware',
          3 => 'Opcodes\\LogViewer\\Http\\Middleware\\JsonResourceWithoutWrappingMiddleware',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\LogsController@index',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\LogsController@index',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.logs',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.folders.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/log-viewer/api/folders/{folderIdentifier}/download',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Illuminate\\Routing\\Middleware\\ValidateSignature',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FoldersController@download',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FoldersController@download',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.folders.download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.files.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/log-viewer/api/files/{fileIdentifier}/download',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Opcodes\\LogViewer\\Http\\Middleware\\EnsureFrontendRequestsAreStateful',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
          2 => 'Illuminate\\Routing\\Middleware\\ValidateSignature',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@download',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\FilesController@download',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer/api',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.files.download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'log-viewer.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/log-viewer/{view?}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'web',
          1 => 'Opcodes\\LogViewer\\Http\\Middleware\\AuthorizeLogViewer',
        ),
        'uses' => 'Opcodes\\LogViewer\\Http\\Controllers\\IndexController@__invoke',
        'controller' => 'Opcodes\\LogViewer\\Http\\Controllers\\IndexController',
        'namespace' => 'Opcodes\\LogViewer\\Http\\Controllers',
        'prefix' => '/admin/log-viewer',
        'where' => 
        array (
        ),
        'as' => 'log-viewer.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'view' => '(.*)',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.healthCheck' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_ignition/health-check',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\HealthCheckController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\HealthCheckController',
        'as' => 'ignition.healthCheck',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.executeSolution' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_ignition/execute-solution',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\ExecuteSolutionController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\ExecuteSolutionController',
        'as' => 'ignition.executeSolution',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.updateConfig' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_ignition/update-config',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\UpdateConfigController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\UpdateConfigController',
        'as' => 'ignition.updateConfig',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::AcqNO9xq9uVIMyzT' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'broadcasting/auth',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => '\\Illuminate\\Broadcasting\\BroadcastController@authenticate',
        'controller' => '\\Illuminate\\Broadcasting\\BroadcastController@authenticate',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
          0 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
        ),
        'as' => 'generated::AcqNO9xq9uVIMyzT',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.dashboard.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.dashboard.index',
        'uses' => 'App\\Http\\Controllers\\Frontend\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\Frontend\\DashboardController@index',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.dashboard.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.dashboard.create',
        'uses' => 'App\\Http\\Controllers\\Frontend\\DashboardController@create',
        'controller' => 'App\\Http\\Controllers\\Frontend\\DashboardController@create',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.dashboard.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.dashboard.store',
        'uses' => 'App\\Http\\Controllers\\Frontend\\DashboardController@store',
        'controller' => 'App\\Http\\Controllers\\Frontend\\DashboardController@store',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.dashboard.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/{dashboard}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.dashboard.show',
        'uses' => 'App\\Http\\Controllers\\Frontend\\DashboardController@show',
        'controller' => 'App\\Http\\Controllers\\Frontend\\DashboardController@show',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.dashboard.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/{dashboard}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.dashboard.edit',
        'uses' => 'App\\Http\\Controllers\\Frontend\\DashboardController@edit',
        'controller' => 'App\\Http\\Controllers\\Frontend\\DashboardController@edit',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.dashboard.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'dashboard/{dashboard}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.dashboard.update',
        'uses' => 'App\\Http\\Controllers\\Frontend\\DashboardController@update',
        'controller' => 'App\\Http\\Controllers\\Frontend\\DashboardController@update',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.dashboard.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'dashboard/{dashboard}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.dashboard.destroy',
        'uses' => 'App\\Http\\Controllers\\Frontend\\DashboardController@destroy',
        'controller' => 'App\\Http\\Controllers\\Frontend\\DashboardController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.airports.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'airports/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\AirportController@show',
        'controller' => 'App\\Http\\Controllers\\Frontend\\AirportController@show',
        'as' => 'frontend.airports.show',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.downloads.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'downloads',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\DownloadController@index',
        'controller' => 'App\\Http\\Controllers\\Frontend\\DownloadController@index',
        'as' => 'frontend.downloads.index',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.downloads.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'downloads/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\DownloadController@show',
        'controller' => 'App\\Http\\Controllers\\Frontend\\DownloadController@show',
        'as' => 'frontend.downloads.download',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.flights.bids' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'flights/bids',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\FlightController@bids',
        'controller' => 'App\\Http\\Controllers\\Frontend\\FlightController@bids',
        'as' => 'frontend.flights.bids',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.flights.search' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'flights/search',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\FlightController@search',
        'controller' => 'App\\Http\\Controllers\\Frontend\\FlightController@search',
        'as' => 'frontend.flights.search',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.flights.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'flights',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.flights.index',
        'uses' => 'App\\Http\\Controllers\\Frontend\\FlightController@index',
        'controller' => 'App\\Http\\Controllers\\Frontend\\FlightController@index',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.flights.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'flights/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.flights.create',
        'uses' => 'App\\Http\\Controllers\\Frontend\\FlightController@create',
        'controller' => 'App\\Http\\Controllers\\Frontend\\FlightController@create',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.flights.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'flights',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.flights.store',
        'uses' => 'App\\Http\\Controllers\\Frontend\\FlightController@store',
        'controller' => 'App\\Http\\Controllers\\Frontend\\FlightController@store',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.flights.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'flights/{flight}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.flights.show',
        'uses' => 'App\\Http\\Controllers\\Frontend\\FlightController@show',
        'controller' => 'App\\Http\\Controllers\\Frontend\\FlightController@show',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.flights.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'flights/{flight}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.flights.edit',
        'uses' => 'App\\Http\\Controllers\\Frontend\\FlightController@edit',
        'controller' => 'App\\Http\\Controllers\\Frontend\\FlightController@edit',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.flights.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'flights/{flight}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.flights.update',
        'uses' => 'App\\Http\\Controllers\\Frontend\\FlightController@update',
        'controller' => 'App\\Http\\Controllers\\Frontend\\FlightController@update',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.flights.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'flights/{flight}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.flights.destroy',
        'uses' => 'App\\Http\\Controllers\\Frontend\\FlightController@destroy',
        'controller' => 'App\\Http\\Controllers\\Frontend\\FlightController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'pireps/fares',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\PirepController@fares',
        'controller' => 'App\\Http\\Controllers\\Frontend\\PirepController@fares',
        'as' => 'frontend.',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pireps.submit' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'pireps/{id}/submit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\PirepController@submit',
        'controller' => 'App\\Http\\Controllers\\Frontend\\PirepController@submit',
        'as' => 'frontend.pireps.submit',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pireps.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'pireps',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.pireps.index',
        'uses' => 'App\\Http\\Controllers\\Frontend\\PirepController@index',
        'controller' => 'App\\Http\\Controllers\\Frontend\\PirepController@index',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pireps.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'pireps/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.pireps.create',
        'uses' => 'App\\Http\\Controllers\\Frontend\\PirepController@create',
        'controller' => 'App\\Http\\Controllers\\Frontend\\PirepController@create',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pireps.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'pireps',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.pireps.store',
        'uses' => 'App\\Http\\Controllers\\Frontend\\PirepController@store',
        'controller' => 'App\\Http\\Controllers\\Frontend\\PirepController@store',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pireps.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'pireps/{pirep}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.pireps.edit',
        'uses' => 'App\\Http\\Controllers\\Frontend\\PirepController@edit',
        'controller' => 'App\\Http\\Controllers\\Frontend\\PirepController@edit',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pireps.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'pireps/{pirep}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.pireps.update',
        'uses' => 'App\\Http\\Controllers\\Frontend\\PirepController@update',
        'controller' => 'App\\Http\\Controllers\\Frontend\\PirepController@update',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pireps.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'pireps/{pirep}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.pireps.destroy',
        'uses' => 'App\\Http\\Controllers\\Frontend\\PirepController@destroy',
        'controller' => 'App\\Http\\Controllers\\Frontend\\PirepController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.profile.acars' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'profile/acars',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@acars',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@acars',
        'as' => 'frontend.profile.acars',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.profile.regen_apikey' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'profile/regen_apikey',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@regen_apikey',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@regen_apikey',
        'as' => 'frontend.profile.regen_apikey',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.profile.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.profile.index',
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@index',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@index',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.profile.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'profile/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.profile.create',
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@create',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@create',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.profile.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.profile.store',
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@store',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@store',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.profile.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'profile/{profile}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.profile.show',
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@show',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@show',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.profile.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'profile/{profile}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.profile.edit',
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@edit',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@edit',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.profile.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'profile/{profile}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.profile.update',
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@update',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@update',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.profile.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'profile/{profile}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'as' => 'frontend.profile.destroy',
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@destroy',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.simbrief.generate' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'simbrief/generate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@generate',
        'controller' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@generate',
        'as' => 'frontend.simbrief.generate',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.simbrief.api_code' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'simbrief/apicode',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@api_code',
        'controller' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@api_code',
        'as' => 'frontend.simbrief.api_code',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.simbrief.check_ofp' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'simbrief/check_ofp',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
          3 => 'throttle:10,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@check_ofp',
        'controller' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@check_ofp',
        'as' => 'frontend.simbrief.check_ofp',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.simbrief.update_ofp' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'simbrief/update_ofp',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@update_ofp',
        'controller' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@update_ofp',
        'as' => 'frontend.simbrief.update_ofp',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.simbrief.briefing' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'simbrief/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@briefing',
        'controller' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@briefing',
        'as' => 'frontend.simbrief.briefing',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.simbrief.prefile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'simbrief/{id}/prefile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@prefile',
        'controller' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@prefile',
        'as' => 'frontend.simbrief.prefile',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.simbrief.cancel' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'simbrief/{id}/cancel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@cancel',
        'controller' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@cancel',
        'as' => 'frontend.simbrief.cancel',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.simbrief.generate_new' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'simbrief/{id}/generate_new',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@generate_new',
        'controller' => 'App\\Http\\Controllers\\Frontend\\SimBriefController@generate_new',
        'as' => 'frontend.simbrief.generate_new',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.home' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\HomeController@index',
        'controller' => 'App\\Http\\Controllers\\Frontend\\HomeController@index',
        'as' => 'frontend.home',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pirep.show.public' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'r/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\PirepController@show',
        'controller' => 'App\\Http\\Controllers\\Frontend\\PirepController@show',
        'as' => 'frontend.pirep.show.public',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pireps.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'pireps/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\PirepController@show',
        'controller' => 'App\\Http\\Controllers\\Frontend\\PirepController@show',
        'as' => 'frontend.pireps.show',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.users.show.public' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@show',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@show',
        'as' => 'frontend.users.show.public',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pilots.show.public' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'pilots/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@show',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@show',
        'as' => 'frontend.pilots.show.public',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pages.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'page/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\PageController@show',
        'controller' => 'App\\Http\\Controllers\\Frontend\\PageController@show',
        'as' => 'frontend.pages.show',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.profile.show.public' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'profile/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\ProfileController@show',
        'controller' => 'App\\Http\\Controllers\\Frontend\\ProfileController@show',
        'as' => 'frontend.profile.show.public',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\UserController@index',
        'controller' => 'App\\Http\\Controllers\\Frontend\\UserController@index',
        'as' => 'frontend.users.index',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.pilots.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'pilots',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\UserController@index',
        'controller' => 'App\\Http\\Controllers\\Frontend\\UserController@index',
        'as' => 'frontend.pilots.index',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.livemap.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'livemap',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\LiveMapController@index',
        'controller' => 'App\\Http\\Controllers\\Frontend\\LiveMapController@index',
        'as' => 'frontend.livemap.index',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.lang.switch' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'lang/{lang}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\LanguageController@switchLang',
        'controller' => 'App\\Http\\Controllers\\Frontend\\LanguageController@switchLang',
        'as' => 'frontend.lang.switch',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'frontend.credits' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'credits',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Frontend\\CreditsController@index',
        'controller' => 'App\\Http\\Controllers\\Frontend\\CreditsController@index',
        'as' => 'frontend.credits',
        'namespace' => 'App\\Http\\Controllers\\Frontend',
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'oauth.redirect' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'oauth/{provider}/redirect',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\OAuthController@redirectToProvider',
        'controller' => 'App\\Http\\Controllers\\Auth\\OAuthController@redirectToProvider',
        'as' => 'oauth.redirect',
        'namespace' => 'App\\Http\\Controllers\\Auth',
        'prefix' => '/oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'oauth.callback' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'oauth/{provider}/callback',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\OAuthController@handleProviderCallback',
        'controller' => 'App\\Http\\Controllers\\Auth\\OAuthController@handleProviderCallback',
        'as' => 'oauth.callback',
        'namespace' => 'App\\Http\\Controllers\\Auth',
        'prefix' => '/oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'oauth.logout' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'oauth/{provider}/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\OAuthController@logoutProvider',
        'controller' => 'App\\Http\\Controllers\\Auth\\OAuthController@logoutProvider',
        'as' => 'oauth.logout',
        'namespace' => 'App\\Http\\Controllers\\Auth',
        'prefix' => '/oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'auth.logout' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'auth.logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@showLoginForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@showLoginForm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::V3U4Xytvrm3BR2Jx' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@login',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@login',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::V3U4Xytvrm3BR2Jx',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'register' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'register',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\RegisterController@showRegistrationForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\RegisterController@showRegistrationForm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'register',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::OFz90BFAVURRFJLV' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'register',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\RegisterController@register',
        'controller' => 'App\\Http\\Controllers\\Auth\\RegisterController@register',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::OFz90BFAVURRFJLV',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.request' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'password/reset',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@showLinkRequestForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@showLinkRequestForm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.request',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.email' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'password/email',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@sendResetLinkEmail',
        'controller' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@sendResetLinkEmail',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.email',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.reset' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'password/reset/{token}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@showResetForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@showResetForm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.reset',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'password/reset',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@reset',
        'controller' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@reset',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'verification.notice' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'email/verify',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\VerificationController@show',
        'controller' => 'App\\Http\\Controllers\\Auth\\VerificationController@show',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'verification.notice',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'verification.verify' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'email/verify/{id}/{hash}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\VerificationController@verify',
        'controller' => 'App\\Http\\Controllers\\Auth\\VerificationController@verify',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'verification.verify',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'verification.resend' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'email/resend',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\VerificationController@resend',
        'controller' => 'App\\Http\\Controllers\\Auth\\VerificationController@resend',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'verification.resend',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airlines.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airlines',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airlines',
        ),
        'as' => 'admin.airlines.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirlinesController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirlinesController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airlines.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airlines/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airlines',
        ),
        'as' => 'admin.airlines.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirlinesController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirlinesController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airlines.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/airlines',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airlines',
        ),
        'as' => 'admin.airlines.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirlinesController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirlinesController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airlines.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airlines/{airline}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airlines',
        ),
        'as' => 'admin.airlines.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirlinesController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirlinesController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airlines.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airlines/{airline}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airlines',
        ),
        'as' => 'admin.airlines.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirlinesController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirlinesController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airlines.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/airlines/{airline}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airlines',
        ),
        'as' => 'admin.airlines.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirlinesController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirlinesController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airlines.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/airlines/{airline}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airlines',
        ),
        'as' => 'admin.airlines.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirlinesController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirlinesController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/roles',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'role:admin',
        ),
        'as' => 'admin.roles.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\RolesController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\RolesController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/roles/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'role:admin',
        ),
        'as' => 'admin.roles.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\RolesController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\RolesController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/roles',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'role:admin',
        ),
        'as' => 'admin.roles.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\RolesController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\RolesController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/roles/{role}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'role:admin',
        ),
        'as' => 'admin.roles.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\RolesController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\RolesController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/roles/{role}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'role:admin',
        ),
        'as' => 'admin.roles.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\RolesController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\RolesController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/roles/{role}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'role:admin',
        ),
        'as' => 'admin.roles.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\RolesController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\RolesController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/roles/{role}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'role:admin',
        ),
        'as' => 'admin.roles.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\RolesController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\RolesController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airports.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airports/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airports',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AirportController@export',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirportController@export',
        'as' => 'admin.airports.export',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'HEAD',
      ),
      'uri' => 'admin/airports/fuel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airports',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AirportController@fuel',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirportController@fuel',
        'as' => 'admin.',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airports.import' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/airports/import',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airports',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AirportController@import',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirportController@import',
        'as' => 'admin.airports.import',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::0JXVVZma8UwISxzX' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/airports/{id}/expenses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airports',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AirportController@expenses',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirportController@expenses',
        'as' => 'admin.generated::0JXVVZma8UwISxzX',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airports.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airports',
        ),
        'as' => 'admin.airports.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirportController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirportController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airports.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airports/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airports',
        ),
        'as' => 'admin.airports.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirportController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirportController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airports.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/airports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airports',
        ),
        'as' => 'admin.airports.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirportController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirportController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airports.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airports/{airport}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airports',
        ),
        'as' => 'admin.airports.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirportController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirportController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airports.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airports/{airport}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airports',
        ),
        'as' => 'admin.airports.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirportController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirportController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airports.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/airports/{airport}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airports',
        ),
        'as' => 'admin.airports.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirportController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirportController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airports.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/airports/{airport}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,airports',
        ),
        'as' => 'admin.airports.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirportController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirportController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/awards',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,awards',
        ),
        'as' => 'admin.awards.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\AwardController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\AwardController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/awards/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,awards',
        ),
        'as' => 'admin.awards.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\AwardController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\AwardController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/awards',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,awards',
        ),
        'as' => 'admin.awards.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\AwardController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\AwardController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/awards/{award}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,awards',
        ),
        'as' => 'admin.awards.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\AwardController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\AwardController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/awards/{award}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,awards',
        ),
        'as' => 'admin.awards.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\AwardController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\AwardController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/awards/{award}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,awards',
        ),
        'as' => 'admin.awards.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\AwardController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\AwardController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/awards/{award}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,awards',
        ),
        'as' => 'admin.awards.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\AwardController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\AwardController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.aircraft.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/aircraft/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AircraftController@export',
        'controller' => 'App\\Http\\Controllers\\Admin\\AircraftController@export',
        'as' => 'admin.aircraft.export',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.aircraft.import' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/aircraft/import',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AircraftController@import',
        'controller' => 'App\\Http\\Controllers\\Admin\\AircraftController@import',
        'as' => 'admin.aircraft.import',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::5yU6ygvDB8B7J4jC' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/aircraft/{id}/expenses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AircraftController@expenses',
        'controller' => 'App\\Http\\Controllers\\Admin\\AircraftController@expenses',
        'as' => 'admin.generated::5yU6ygvDB8B7J4jC',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.aircraft.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/aircraft',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft',
        ),
        'as' => 'admin.aircraft.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\AircraftController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\AircraftController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.aircraft.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/aircraft/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft',
        ),
        'as' => 'admin.aircraft.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\AircraftController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\AircraftController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.aircraft.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/aircraft',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft',
        ),
        'as' => 'admin.aircraft.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\AircraftController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\AircraftController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.aircraft.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/aircraft/{aircraft}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft',
        ),
        'as' => 'admin.aircraft.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\AircraftController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\AircraftController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.aircraft.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/aircraft/{aircraft}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft',
        ),
        'as' => 'admin.aircraft.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\AircraftController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\AircraftController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.aircraft.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/aircraft/{aircraft}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft',
        ),
        'as' => 'admin.aircraft.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\AircraftController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\AircraftController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.aircraft.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/aircraft/{aircraft}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft',
        ),
        'as' => 'admin.aircraft.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\AircraftController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\AircraftController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.aircraft.trashbin' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/aircraft/trashbin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AircraftController@trashbin',
        'controller' => 'App\\Http\\Controllers\\Admin\\AircraftController@trashbin',
        'as' => 'admin.aircraft.trashbin',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.expenses.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/expenses/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ExpenseController@export',
        'controller' => 'App\\Http\\Controllers\\Admin\\ExpenseController@export',
        'as' => 'admin.expenses.export',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.expenses.import' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/expenses/import',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ExpenseController@import',
        'controller' => 'App\\Http\\Controllers\\Admin\\ExpenseController@import',
        'as' => 'admin.expenses.import',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.expenses.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/expenses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.expenses.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\ExpenseController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ExpenseController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.expenses.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/expenses/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.expenses.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\ExpenseController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\ExpenseController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.expenses.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/expenses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.expenses.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\ExpenseController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\ExpenseController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.expenses.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/expenses/{expense}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.expenses.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\ExpenseController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\ExpenseController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.expenses.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/expenses/{expense}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.expenses.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\ExpenseController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\ExpenseController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.expenses.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/expenses/{expense}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.expenses.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\ExpenseController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\ExpenseController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.expenses.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/expenses/{expense}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.expenses.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\ExpenseController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\ExpenseController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fares.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fares/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FareController@export',
        'controller' => 'App\\Http\\Controllers\\Admin\\FareController@export',
        'as' => 'admin.fares.export',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fares.import' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/fares/import',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FareController@import',
        'controller' => 'App\\Http\\Controllers\\Admin\\FareController@import',
        'as' => 'admin.fares.import',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fares.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fares',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.fares.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\FareController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\FareController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fares.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fares/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.fares.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\FareController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\FareController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fares.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/fares',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.fares.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\FareController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\FareController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fares.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fares/{fare}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.fares.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\FareController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\FareController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fares.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fares/{fare}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.fares.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\FareController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\FareController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fares.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/fares/{fare}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.fares.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\FareController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\FareController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fares.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/fares/{fare}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.fares.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\FareController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\FareController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fares.trashbin' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/fares/trashbin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FareController@trashbin',
        'controller' => 'App\\Http\\Controllers\\Admin\\FareController@trashbin',
        'as' => 'admin.fares.trashbin',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.files.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/files',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,files',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FileController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\FileController@store',
        'as' => 'admin.files.store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.files.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/files/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,files',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FileController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\FileController@destroy',
        'as' => 'admin.files.delete',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.finances.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/finances',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.finances.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\FinanceController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\FinanceController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.finances.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/finances/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.finances.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\FinanceController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\FinanceController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.finances.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/finances',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.finances.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\FinanceController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\FinanceController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.finances.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/finances/{finance}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.finances.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\FinanceController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\FinanceController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.finances.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/finances/{finance}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.finances.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\FinanceController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\FinanceController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.finances.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/finances/{finance}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.finances.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\FinanceController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\FinanceController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.finances.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/finances/{finance}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,finances',
        ),
        'as' => 'admin.finances.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\FinanceController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\FinanceController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flights.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/flights/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@export',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@export',
        'as' => 'admin.flights.export',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flights.import' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/flights/import',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@import',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@import',
        'as' => 'admin.flights.import',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::teqbimvQgDZFjMv4' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/flights/{id}/fares',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@fares',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@fares',
        'as' => 'admin.generated::teqbimvQgDZFjMv4',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::4v2lsffM12tf2aiV' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/flights/{id}/fields',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@field_values',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@field_values',
        'as' => 'admin.generated::4v2lsffM12tf2aiV',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::jY0FtpAFpVxH27DW' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/flights/{id}/subfleets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@subfleets',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@subfleets',
        'as' => 'admin.generated::jY0FtpAFpVxH27DW',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flights.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/flights',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flights.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flights.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/flights/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flights.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flights.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/flights',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flights.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flights.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/flights/{flight}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flights.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flights.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/flights/{flight}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flights.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flights.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/flights/{flight}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flights.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flights.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/flights/{flight}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flights.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flightfields.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/flightfields',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flightfields.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flightfields.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/flightfields/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flightfields.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flightfields.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/flightfields',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flightfields.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flightfields.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/flightfields/{flightfield}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flightfields.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flightfields.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/flightfields/{flightfield}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flightfields.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flightfields.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/flightfields/{flightfield}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flightfields.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.flightfields.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/flightfields/{flightfield}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,flights',
        ),
        'as' => 'admin.flightfields.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\FlightFieldController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.userfields.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/userfields',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.userfields.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserFieldController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserFieldController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.userfields.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/userfields/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.userfields.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserFieldController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserFieldController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.userfields.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/userfields',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.userfields.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserFieldController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserFieldController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.userfields.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/userfields/{userfield}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.userfields.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserFieldController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserFieldController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.userfields.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/userfields/{userfield}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.userfields.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserFieldController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserFieldController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.userfields.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/userfields/{userfield}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.userfields.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserFieldController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserFieldController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.userfields.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/userfields/{userfield}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.userfields.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserFieldController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserFieldController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::KxuPs1WOGMFn4RQQ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pireps/fares',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepController@fares',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepController@fares',
        'as' => 'admin.generated::KxuPs1WOGMFn4RQQ',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::bffWcGNR7NMVfZhs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pireps/pending',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepController@pending',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepController@pending',
        'as' => 'admin.generated::bffWcGNR7NMVfZhs',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pireps.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pireps',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pireps.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pireps.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pireps/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pireps.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pireps.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/pireps',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pireps.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pireps.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pireps/{pirep}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pireps.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pireps.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pireps/{pirep}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pireps.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pireps.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/pireps/{pirep}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pireps.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pireps.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/pireps/{pirep}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pireps.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::UUriHB2JvD0lGbNC' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'DELETE',
        3 => 'HEAD',
      ),
      'uri' => 'admin/pireps/{id}/comments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepController@comments',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepController@comments',
        'as' => 'admin.generated::UUriHB2JvD0lGbNC',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pirep.status' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
        1 => 'PUT',
      ),
      'uri' => 'admin/pireps/{id}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepController@status',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepController@status',
        'as' => 'admin.pirep.status',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pirepfields.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pirepfields',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pirepfields.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pirepfields.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pirepfields/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pirepfields.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pirepfields.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/pirepfields',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pirepfields.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pirepfields.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pirepfields/{pirepfield}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pirepfields.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pirepfields.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pirepfields/{pirepfield}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pirepfields.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pirepfields.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/pirepfields/{pirepfield}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pirepfields.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pirepfields.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/pirepfields/{pirepfield}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pireps',
        ),
        'as' => 'admin.pirepfields.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\PirepFieldController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pages.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pages',
        ),
        'as' => 'admin.pages.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\PagesController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\PagesController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pages.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pages/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pages',
        ),
        'as' => 'admin.pages.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\PagesController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\PagesController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pages.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/pages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pages',
        ),
        'as' => 'admin.pages.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\PagesController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\PagesController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pages.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pages/{page}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pages',
        ),
        'as' => 'admin.pages.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\PagesController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\PagesController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pages.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pages/{page}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pages',
        ),
        'as' => 'admin.pages.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\PagesController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\PagesController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pages.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/pages/{page}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pages',
        ),
        'as' => 'admin.pages.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\PagesController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\PagesController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pages.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/pages/{page}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,pages',
        ),
        'as' => 'admin.pages.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\PagesController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\PagesController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ranks.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/ranks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,ranks',
        ),
        'as' => 'admin.ranks.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\RankController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\RankController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ranks.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/ranks/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,ranks',
        ),
        'as' => 'admin.ranks.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\RankController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\RankController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ranks.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/ranks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,ranks',
        ),
        'as' => 'admin.ranks.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\RankController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\RankController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ranks.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/ranks/{rank}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,ranks',
        ),
        'as' => 'admin.ranks.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\RankController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\RankController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ranks.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/ranks/{rank}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,ranks',
        ),
        'as' => 'admin.ranks.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\RankController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\RankController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ranks.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/ranks/{rank}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,ranks',
        ),
        'as' => 'admin.ranks.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\RankController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\RankController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ranks.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/ranks/{rank}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,ranks',
        ),
        'as' => 'admin.ranks.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\RankController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\RankController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::ng8rtoiI2utJOhEN' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/ranks/{id}/subfleets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,ranks',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\RankController@subfleets',
        'controller' => 'App\\Http\\Controllers\\Admin\\RankController@subfleets',
        'as' => 'admin.generated::ng8rtoiI2utJOhEN',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::fGhkCpvK4XCwu1QV' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,settings',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingsController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingsController@index',
        'as' => 'admin.generated::fGhkCpvK4XCwu1QV',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
        1 => 'PUT',
      ),
      'uri' => 'admin/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,settings',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingsController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingsController@update',
        'as' => 'admin.settings.update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.typeratings.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/typeratings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,typeratings',
        ),
        'as' => 'admin.typeratings.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.typeratings.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/typeratings/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,typeratings',
        ),
        'as' => 'admin.typeratings.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.typeratings.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/typeratings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,typeratings',
        ),
        'as' => 'admin.typeratings.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.typeratings.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/typeratings/{typerating}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,typeratings',
        ),
        'as' => 'admin.typeratings.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.typeratings.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/typeratings/{typerating}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,typeratings',
        ),
        'as' => 'admin.typeratings.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.typeratings.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/typeratings/{typerating}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,typeratings',
        ),
        'as' => 'admin.typeratings.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.typeratings.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/typeratings/{typerating}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,typeratings',
        ),
        'as' => 'admin.typeratings.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::9L1QmtKnZdrHJ7D6' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/typeratings/{id}/subfleets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,typeratings',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@subfleets',
        'controller' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@subfleets',
        'as' => 'admin.generated::9L1QmtKnZdrHJ7D6',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::4HCg2D4NPEVLjRU2' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/typeratings/{id}/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,typeratings',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@users',
        'controller' => 'App\\Http\\Controllers\\Admin\\TypeRatingController@users',
        'as' => 'admin.generated::4HCg2D4NPEVLjRU2',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airframes.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airframes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft,fleet',
        ),
        'as' => 'admin.airframes.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirframeController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirframeController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airframes.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airframes/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft,fleet',
        ),
        'as' => 'admin.airframes.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirframeController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirframeController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airframes.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/airframes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft,fleet',
        ),
        'as' => 'admin.airframes.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirframeController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirframeController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airframes.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airframes/{airframe}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft,fleet',
        ),
        'as' => 'admin.airframes.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirframeController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirframeController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airframes.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/airframes/{airframe}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft,fleet',
        ),
        'as' => 'admin.airframes.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirframeController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirframeController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airframes.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/airframes/{airframe}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft,fleet',
        ),
        'as' => 'admin.airframes.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirframeController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirframeController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airframes.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/airframes/{airframe}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft,fleet',
        ),
        'as' => 'admin.airframes.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\AirframeController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirframeController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.airframes.sbupdate' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/sbupdate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,aircraft,fleet',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\AirframeController@updateSimbriefData',
        'controller' => 'App\\Http\\Controllers\\Admin\\AirframeController@updateSimbriefData',
        'as' => 'admin.airframes.sbupdate',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maintenance.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/maintenance',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,maintenance',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@index',
        'as' => 'admin.maintenance.index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maintenance.cache' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/maintenance/cache',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,maintenance',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@cache',
        'controller' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@cache',
        'as' => 'admin.maintenance.cache',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maintenance.queue' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/maintenance/queue',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,maintenance',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@queue',
        'controller' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@queue',
        'as' => 'admin.maintenance.queue',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maintenance.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/maintenance/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,maintenance',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@update',
        'as' => 'admin.maintenance.update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maintenance.forcecheck' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/maintenance/forcecheck',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,maintenance',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@forcecheck',
        'controller' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@forcecheck',
        'as' => 'admin.maintenance.forcecheck',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maintenance.reseed' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/maintenance/reseed',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,maintenance',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@reseed',
        'controller' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@reseed',
        'as' => 'admin.maintenance.reseed',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maintenance.cron_enable' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/maintenance/cron_enable',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,maintenance',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@cron_enable',
        'controller' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@cron_enable',
        'as' => 'admin.maintenance.cron_enable',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maintenance.cron_disable' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/maintenance/cron_disable',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,maintenance',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@cron_disable',
        'controller' => 'App\\Http\\Controllers\\Admin\\MaintenanceController@cron_disable',
        'as' => 'admin.maintenance.cron_disable',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.subfleets.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/subfleets/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@export',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@export',
        'as' => 'admin.subfleets.export',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.subfleets.import' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/subfleets/import',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@import',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@import',
        'as' => 'admin.subfleets.import',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::EbQG37mG5XoNn2C7' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/subfleets/{id}/expenses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@expenses',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@expenses',
        'as' => 'admin.generated::EbQG37mG5XoNn2C7',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::Fi9Py65u8BmAWjbt' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/subfleets/{id}/fares',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@fares',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@fares',
        'as' => 'admin.generated::Fi9Py65u8BmAWjbt',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::WkfDz566Ccr9fnB8' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/subfleets/{id}/ranks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@ranks',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@ranks',
        'as' => 'admin.generated::WkfDz566Ccr9fnB8',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::H62BiHW89qnzEcbw' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/subfleets/{id}/typeratings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@typeratings',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@typeratings',
        'as' => 'admin.generated::H62BiHW89qnzEcbw',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.subfleets.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/subfleets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'as' => 'admin.subfleets.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.subfleets.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/subfleets/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'as' => 'admin.subfleets.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.subfleets.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/subfleets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'as' => 'admin.subfleets.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.subfleets.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/subfleets/{subfleet}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'as' => 'admin.subfleets.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.subfleets.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/subfleets/{subfleet}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'as' => 'admin.subfleets.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.subfleets.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/subfleets/{subfleet}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'as' => 'admin.subfleets.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.subfleets.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/subfleets/{subfleet}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'as' => 'admin.subfleets.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.subfleets.trashbin' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/subfleets/trashbin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,fleet',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SubfleetController@trashbin',
        'controller' => 'App\\Http\\Controllers\\Admin\\SubfleetController@trashbin',
        'as' => 'admin.subfleets.trashbin',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.destroy_user_award' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/users/{id}/award/{award_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@destroy_user_award',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@destroy_user_award',
        'as' => 'admin.users.destroy_user_award',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.regen_apikey' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{id}/regen_apikey',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@regen_apikey',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@regen_apikey',
        'as' => 'admin.users.regen_apikey',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.verify_email' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{id}/verify_email',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@verify_email',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@verify_email',
        'as' => 'admin.users.verify_email',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.request_email_verification' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{id}/request_email_verification',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@request_email_verification',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@request_email_verification',
        'as' => 'admin.users.request_email_verification',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.users.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.users.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.users.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.users.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{user}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.users.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.users.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.users.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invites.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/invites',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.invites.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\InviteController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\InviteController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invites.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/invites/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.invites.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\InviteController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\InviteController@create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invites.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/invites',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.invites.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\InviteController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\InviteController@store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.invites.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/invites/{invite}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'as' => 'admin.invites.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\InviteController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\InviteController@destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::KcviFz9i7w34FNUt' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'PUT',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/users/{id}/typeratings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@typeratings',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@typeratings',
        'as' => 'admin.generated::KcviFz9i7w34FNUt',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::UFwqohDvWHGMQz87' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'update_pending',
          5 => 'ability:admin,admin-access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\DashboardController@index',
        'as' => 'admin.generated::UFwqohDvWHGMQz87',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::h7dMrQ1FlC6Zxybh' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'update_pending',
          5 => 'ability:admin,admin-access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DashboardController@index',
        'name' => 'dashboard',
        'controller' => 'App\\Http\\Controllers\\Admin\\DashboardController@index',
        'as' => 'admin.generated::h7dMrQ1FlC6Zxybh',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.dashboard.news' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'PATCH',
        2 => 'POST',
        3 => 'DELETE',
        4 => 'HEAD',
      ),
      'uri' => 'admin/dashboard/news',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'update_pending',
          5 => 'ability:admin,admin-access',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DashboardController@news',
        'controller' => 'App\\Http\\Controllers\\Admin\\DashboardController@news',
        'as' => 'admin.dashboard.news',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.activities.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/activities',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,admin-access',
        ),
        'as' => 'admin.activities.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\ActivityController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ActivityController@index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.activities.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/activities/{activity}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,admin-access',
        ),
        'as' => 'admin.activities.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\ActivityController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\ActivityController@show',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.modules.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,modules',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ModulesController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ModulesController@index',
        'as' => 'admin.modules.index',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.modules.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/modules/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,modules',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ModulesController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\ModulesController@create',
        'as' => 'admin.modules.create',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.modules.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/modules/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,modules',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ModulesController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\ModulesController@store',
        'as' => 'admin.modules.store',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.modules.enable' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/modules/enable',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,modules',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ModulesController@enable',
        'controller' => 'App\\Http\\Controllers\\Admin\\ModulesController@enable',
        'as' => 'admin.modules.enable',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.modules.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/modules/{id}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,modules',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ModulesController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\ModulesController@edit',
        'as' => 'admin.modules.edit',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.modules.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/modules/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,modules',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ModulesController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\ModulesController@update',
        'as' => 'admin.modules.update',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.modules.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/modules/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'App\\Http\\Middleware\\EnableActivityLogging',
          4 => 'ability:admin,modules',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ModulesController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\ModulesController@destroy',
        'as' => 'admin.modules.destroy',
        'namespace' => 'App\\Http\\Controllers\\Admin',
        'prefix' => 'admin/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\StatusController@status',
        'controller' => 'App\\Http\\Controllers\\Api\\StatusController@status',
        'as' => 'api.',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::rQeyKbjtf2eSTaBs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/acars',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AcarsController@live_flights',
        'controller' => 'App\\Http\\Controllers\\Api\\AcarsController@live_flights',
        'as' => 'api.generated::rQeyKbjtf2eSTaBs',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::t8FBKA8c2Rbn1UA2' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/acars/geojson',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AcarsController@pireps_geojson',
        'controller' => 'App\\Http\\Controllers\\Api\\AcarsController@pireps_geojson',
        'as' => 'api.generated::t8FBKA8c2Rbn1UA2',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::M7mYar6kiHZElhV3' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/airports/hubs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AirportController@index_hubs',
        'controller' => 'App\\Http\\Controllers\\Api\\AirportController@index_hubs',
        'as' => 'api.generated::M7mYar6kiHZElhV3',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::kK6eilrReeqA7Wtj' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/airports/search',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AirportController@search',
        'controller' => 'App\\Http\\Controllers\\Api\\AirportController@search',
        'as' => 'api.generated::kK6eilrReeqA7Wtj',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::vbqmWAVuOgedhSOw' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/pireps/{pirep_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@get',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@get',
        'as' => 'api.generated::vbqmWAVuOgedhSOw',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::koLXS6B3k2oXQ0OR' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/pireps/{pirep_id}/acars/geojson',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_geojson',
        'controller' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_geojson',
        'as' => 'api.generated::koLXS6B3k2oXQ0OR',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.maintenance.cron' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/cron/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\MaintenanceController@cron',
        'controller' => 'App\\Http\\Controllers\\Api\\MaintenanceController@cron',
        'as' => 'api.maintenance.cron',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::xRYZaLFyVUuPQ2kK' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/news',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\NewsController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\NewsController@index',
        'as' => 'api.generated::xRYZaLFyVUuPQ2kK',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::7syfxuNGe07YtBpu' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\StatusController@status',
        'controller' => 'App\\Http\\Controllers\\Api\\StatusController@status',
        'as' => 'api.generated::7syfxuNGe07YtBpu',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::F3ys03MzFYGrD0Av' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/version',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\StatusController@status',
        'controller' => 'App\\Http\\Controllers\\Api\\StatusController@status',
        'as' => 'api.generated::F3ys03MzFYGrD0Av',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::mtvMKSRcX4TiAd7b' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/airlines',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AirlineController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\AirlineController@index',
        'as' => 'api.generated::mtvMKSRcX4TiAd7b',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::0wUYxJwDdVClYqHm' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/airlines/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AirlineController@get',
        'controller' => 'App\\Http\\Controllers\\Api\\AirlineController@get',
        'as' => 'api.generated::0wUYxJwDdVClYqHm',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::b08DWrEl8lbNszEv' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/airports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AirportController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\AirportController@index',
        'as' => 'api.generated::b08DWrEl8lbNszEv',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::VYGxjQmz5sFeMgnT' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/airports/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AirportController@get',
        'controller' => 'App\\Http\\Controllers\\Api\\AirportController@get',
        'as' => 'api.generated::VYGxjQmz5sFeMgnT',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::lG4uyIUXgoOiwQIQ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/airports/{id}/lookup',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AirportController@lookup',
        'controller' => 'App\\Http\\Controllers\\Api\\AirportController@lookup',
        'as' => 'api.generated::lG4uyIUXgoOiwQIQ',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::ue3XjoRiUArIWEWX' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/airports/{id}/distance/{to}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AirportController@distance',
        'controller' => 'App\\Http\\Controllers\\Api\\AirportController@distance',
        'as' => 'api.generated::ue3XjoRiUArIWEWX',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::7QMrSrs6WbDc4VZu' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/fleet',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\FleetController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\FleetController@index',
        'as' => 'api.generated::7QMrSrs6WbDc4VZu',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::5Q7oiau4nLhThQe4' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/fleet/aircraft/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\FleetController@get_aircraft',
        'controller' => 'App\\Http\\Controllers\\Api\\FleetController@get_aircraft',
        'as' => 'api.generated::5Q7oiau4nLhThQe4',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::Q1gQPTuhHBmRJVzj' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/subfleet',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\FleetController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\FleetController@index',
        'as' => 'api.generated::Q1gQPTuhHBmRJVzj',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::0006g8ovarlNoGkd' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/subfleet/aircraft/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\FleetController@get_aircraft',
        'controller' => 'App\\Http\\Controllers\\Api\\FleetController@get_aircraft',
        'as' => 'api.generated::0006g8ovarlNoGkd',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::1Mpyg3HEr2a4ou4K' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/flights',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\FlightController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\FlightController@index',
        'as' => 'api.generated::1Mpyg3HEr2a4ou4K',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::7HVfbkKCm63lpLAH' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/flights/search',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\FlightController@search',
        'controller' => 'App\\Http\\Controllers\\Api\\FlightController@search',
        'as' => 'api.generated::7HVfbkKCm63lpLAH',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::WoVjFC8Sonzd1QeE' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/flights/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\FlightController@get',
        'controller' => 'App\\Http\\Controllers\\Api\\FlightController@get',
        'as' => 'api.generated::WoVjFC8Sonzd1QeE',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.flights.briefing' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/flights/{id}/briefing',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\FlightController@briefing',
        'controller' => 'App\\Http\\Controllers\\Api\\FlightController@briefing',
        'as' => 'api.flights.briefing',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::YC0qDYTHhj1QCGa0' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/flights/{id}/route',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\FlightController@route',
        'controller' => 'App\\Http\\Controllers\\Api\\FlightController@route',
        'as' => 'api.generated::YC0qDYTHhj1QCGa0',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::QwZLHjhrKP7SyNes' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/flights/{id}/aircraft',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\FlightController@aircraft',
        'controller' => 'App\\Http\\Controllers\\Api\\FlightController@aircraft',
        'as' => 'api.generated::QwZLHjhrKP7SyNes',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::VdY0hpM0qLyNV1R3' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/pireps',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@pireps',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@pireps',
        'as' => 'api.generated::VdY0hpM0qLyNV1R3',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::FEVcFqjyWPiOVsLR' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/pireps/{pirep_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@update',
        'as' => 'api.generated::FEVcFqjyWPiOVsLR',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::vAiR46HKMSC3Zq2l' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/prefile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@prefile',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@prefile',
        'as' => 'api.generated::vAiR46HKMSC3Zq2l',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::OEU6esQ9aNILxEL1' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/{pirep_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@update',
        'as' => 'api.generated::OEU6esQ9aNILxEL1',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::rADkFxHbUQpRNrXN' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'api/pireps/{pirep_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@update',
        'as' => 'api.generated::rADkFxHbUQpRNrXN',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::sRA0z5GAa1z9tg5t' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/pireps/{pirep_id}/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@update',
        'as' => 'api.generated::sRA0z5GAa1z9tg5t',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::nlOA10txz5TpxbQE' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/{pirep_id}/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@update',
        'as' => 'api.generated::nlOA10txz5TpxbQE',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::9RMFJjOELRze4Ozu' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/{pirep_id}/file',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@file',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@file',
        'as' => 'api.generated::9RMFJjOELRze4Ozu',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::RObCxoA9rgY4gwsF' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/{pirep_id}/comments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@comments_post',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@comments_post',
        'as' => 'api.generated::RObCxoA9rgY4gwsF',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::NOzkZB71q4F2fCzi' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/pireps/{pirep_id}/cancel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@cancel',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@cancel',
        'as' => 'api.generated::NOzkZB71q4F2fCzi',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::ZmbS76OKCNBJuZ3N' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/pireps/{pirep_id}/cancel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@cancel',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@cancel',
        'as' => 'api.generated::ZmbS76OKCNBJuZ3N',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::ZAQNBy3gs3z9rZUi' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/pireps/{pirep_id}/fields',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@fields_get',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@fields_get',
        'as' => 'api.generated::ZAQNBy3gs3z9rZUi',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::6QuvIiIe6LVafqxG' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/{pirep_id}/fields',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@fields_post',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@fields_post',
        'as' => 'api.generated::6QuvIiIe6LVafqxG',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::787FAq2lYCZ0rji3' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/pireps/{pirep_id}/finances',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@finances_get',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@finances_get',
        'as' => 'api.generated::787FAq2lYCZ0rji3',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::yo7Hx1B5ISheVa2v' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/{pirep_id}/finances/recalculate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@finances_recalculate',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@finances_recalculate',
        'as' => 'api.generated::yo7Hx1B5ISheVa2v',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::yBBWHOQIA3xQ8kQz' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/pireps/{pirep_id}/route',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@route_get',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@route_get',
        'as' => 'api.generated::yBBWHOQIA3xQ8kQz',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::htoRZdH2ZBylsNl8' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/{pirep_id}/route',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@route_post',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@route_post',
        'as' => 'api.generated::htoRZdH2ZBylsNl8',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::jDdB8aa7YAErKjho' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/pireps/{pirep_id}/route',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@route_delete',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@route_delete',
        'as' => 'api.generated::jDdB8aa7YAErKjho',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::ZpGcJY4OT1G9h4Ek' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/pireps/{pirep_id}/comments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PirepController@comments_get',
        'controller' => 'App\\Http\\Controllers\\Api\\PirepController@comments_get',
        'as' => 'api.generated::ZpGcJY4OT1G9h4Ek',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::8RplGoNOL8TME0EM' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/pireps/{pirep_id}/acars/position',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_get',
        'controller' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_get',
        'as' => 'api.generated::8RplGoNOL8TME0EM',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::eLQTE1YBZzbyroef' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/{pirep_id}/acars/position',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_store',
        'controller' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_store',
        'as' => 'api.generated::eLQTE1YBZzbyroef',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::cjGa3kB3WmeJOIgz' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/{pirep_id}/acars/positions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_store',
        'controller' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_store',
        'as' => 'api.generated::cjGa3kB3WmeJOIgz',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::JHwp3bCkaooIfVN3' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/{pirep_id}/acars/events',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_events',
        'controller' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_events',
        'as' => 'api.generated::JHwp3bCkaooIfVN3',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::eScJjezwIMhlyv7O' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/pireps/{pirep_id}/acars/logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_logs',
        'controller' => 'App\\Http\\Controllers\\Api\\AcarsController@acars_logs',
        'as' => 'api.generated::eScJjezwIMhlyv7O',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::oFTsrsmcIjr5hpTd' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/user',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@index',
        'as' => 'api.generated::oFTsrsmcIjr5hpTd',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::l3FfDiAo8PB1yRRi' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/user/fleet',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@fleet',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@fleet',
        'as' => 'api.generated::l3FfDiAo8PB1yRRi',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::dIP0YG9NX0ulwO78' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/user/pireps',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@pireps',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@pireps',
        'as' => 'api.generated::dIP0YG9NX0ulwO78',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::A6Fk8i4XjSl0SEqU' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/bids',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'as' => 'api.generated::A6Fk8i4XjSl0SEqU',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::kLvJX9PJBU1TDX6L' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/bids/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@get_bid',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@get_bid',
        'as' => 'api.generated::kLvJX9PJBU1TDX6L',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::yK173lSmEMgLbZhL' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/user/bids/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@get_bid',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@get_bid',
        'as' => 'api.generated::yK173lSmEMgLbZhL',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::DuzQ7Bywm8Uk1n8X' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/user/bids',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'as' => 'api.generated::DuzQ7Bywm8Uk1n8X',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::acgrNjfaa6wGkJ5B' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/user/bids',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'as' => 'api.generated::acgrNjfaa6wGkJ5B',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::T6sNVynp4HdvxdFN' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/user/bids',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'as' => 'api.generated::T6sNVynp4HdvxdFN',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::u6Co68ueLIGv9z3h' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/user/bids',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'as' => 'api.generated::u6Co68ueLIGv9z3h',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::BgTlv6d0e6JkvqOx' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/users/me',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@index',
        'as' => 'api.generated::BgTlv6d0e6JkvqOx',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::atfbv6njt4qdrgb2' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/users/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@get',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@get',
        'as' => 'api.generated::atfbv6njt4qdrgb2',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::tesQU9NpFn6Sgcyj' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/users/{id}/fleet',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@fleet',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@fleet',
        'as' => 'api.generated::tesQU9NpFn6Sgcyj',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::JPLrURvaYGc98ERL' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/users/{id}/pireps',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@pireps',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@pireps',
        'as' => 'api.generated::JPLrURvaYGc98ERL',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::sM5E1PNmJ3OU2XOr' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/users/{id}/bids',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'as' => 'api.generated::sM5E1PNmJ3OU2XOr',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.generated::ZVQyuDXtZaNIv7LY' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/users/{id}/bids',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'controller' => 'App\\Http\\Controllers\\Api\\UserController@bids',
        'as' => 'api.generated::ZVQyuDXtZaNIv7LY',
        'namespace' => 'App\\Http\\Controllers\\Api',
        'prefix' => 'api',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'installer.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'install',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\InstallerController@index',
        'controller' => 'App\\Http\\Controllers\\System\\InstallerController@index',
        'as' => 'installer.index',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'install',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'installer.dbtest' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'install/dbtest',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\InstallerController@dbtest',
        'controller' => 'App\\Http\\Controllers\\System\\InstallerController@dbtest',
        'as' => 'installer.dbtest',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'install',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'installer.step1' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'install/step1',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\InstallerController@step1',
        'controller' => 'App\\Http\\Controllers\\System\\InstallerController@step1',
        'as' => 'installer.step1',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'install',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'installer.step1post' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'install/step1',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\InstallerController@step1',
        'controller' => 'App\\Http\\Controllers\\System\\InstallerController@step1',
        'as' => 'installer.step1post',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'install',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'installer.step2' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'install/step2',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\InstallerController@step2',
        'controller' => 'App\\Http\\Controllers\\System\\InstallerController@step2',
        'as' => 'installer.step2',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'install',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'installer.envsetup' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'install/envsetup',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\InstallerController@envsetup',
        'controller' => 'App\\Http\\Controllers\\System\\InstallerController@envsetup',
        'as' => 'installer.envsetup',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'install',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'installer.dbsetup' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'install/dbsetup',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\InstallerController@dbsetup',
        'controller' => 'App\\Http\\Controllers\\System\\InstallerController@dbsetup',
        'as' => 'installer.dbsetup',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'install',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'installer.step3' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'install/step3',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\InstallerController@step3',
        'controller' => 'App\\Http\\Controllers\\System\\InstallerController@step3',
        'as' => 'installer.step3',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'install',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'installer.usersetup' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'install/usersetup',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\InstallerController@usersetup',
        'controller' => 'App\\Http\\Controllers\\System\\InstallerController@usersetup',
        'as' => 'installer.usersetup',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'install',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'installer.complete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'install/complete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\InstallerController@complete',
        'controller' => 'App\\Http\\Controllers\\System\\InstallerController@complete',
        'as' => 'installer.complete',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'install',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'update.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\UpdateController@index',
        'controller' => 'App\\Http\\Controllers\\System\\UpdateController@index',
        'as' => 'update.index',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'update',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'update.step1' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'update/step1',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\UpdateController@step1',
        'controller' => 'App\\Http\\Controllers\\System\\UpdateController@step1',
        'as' => 'update.step1',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'update',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'update.step1post' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'update/step1',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\UpdateController@step1',
        'controller' => 'App\\Http\\Controllers\\System\\UpdateController@step1',
        'as' => 'update.step1post',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'update',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'update.run_migrations' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'update/run-migrations',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\UpdateController@run_migrations',
        'controller' => 'App\\Http\\Controllers\\System\\UpdateController@run_migrations',
        'as' => 'update.run_migrations',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'update',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'update.complete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'update/complete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
        ),
        'uses' => 'App\\Http\\Controllers\\System\\UpdateController@complete',
        'controller' => 'App\\Http\\Controllers\\System\\UpdateController@complete',
        'as' => 'update.complete',
        'namespace' => 'App\\Http\\Controllers\\System',
        'prefix' => 'update',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DAirports.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dairports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access,addons,modules,airports',
        ),
        'uses' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@index',
        'controller' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@index',
        'as' => 'DAirports.index',
        'namespace' => 'Modules\\DisposableAirports\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DAirports.destroy_airport' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dairports/destroy/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access,addons,modules,airports',
        ),
        'uses' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@destroy',
        'controller' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@destroy',
        'as' => 'DAirports.destroy_airport',
        'namespace' => 'Modules\\DisposableAirports\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DAirports.restore_airport' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dairports/restore/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access,addons,modules,airports',
        ),
        'uses' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@restore',
        'controller' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@restore',
        'as' => 'DAirports.restore_airport',
        'namespace' => 'Modules\\DisposableAirports\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DAirports.update_all' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dairports/update_all',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access,addons,modules,airports',
        ),
        'uses' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@update_all',
        'controller' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@update_all',
        'as' => 'DAirports.update_all',
        'namespace' => 'Modules\\DisposableAirports\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DAirports.update_airport' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dairports/update/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access,addons,modules,airports',
        ),
        'uses' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@update_airport',
        'controller' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@update_airport',
        'as' => 'DAirports.update_airport',
        'namespace' => 'Modules\\DisposableAirports\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DAirports.module_index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/disposableairports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access,addons,modules,airports',
        ),
        'uses' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@index',
        'controller' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@index',
        'as' => 'DAirports.module_index',
        'namespace' => 'Modules\\DisposableAirports\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.settings_update' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dsettings_update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@settings_update',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@settings_update',
        'as' => 'DBasic.settings_update',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DAirports.fix_uzbekistan' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dairports/fix_uz',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access,addons,modules,airports',
        ),
        'uses' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@fix_uzbekistan_airports',
        'controller' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@fix_uzbekistan_airports',
        'as' => 'DAirports.fix_uzbekistan',
        'namespace' => 'Modules\\DisposableAirports\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DAirports.cleanup_airports' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dairports/cleanup',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access,addons,modules,airports',
        ),
        'uses' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@cleanup_airports',
        'controller' => 'Modules\\DisposableAirports\\Http\\Controllers\\DA_AirportController@cleanup_airports',
        'as' => 'DAirports.cleanup_airports',
        'namespace' => 'Modules\\DisposableAirports\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.airlines' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dairlines',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AirlineController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AirlineController@index',
        'as' => 'DBasic.airlines',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.airline' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dairlines/{icao}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AirlineController@show',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AirlineController@show',
        'as' => 'DBasic.airline',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.myairline' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dairline/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AirlineController@myairline',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AirlineController@myairline',
        'as' => 'DBasic.myairline',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.awards' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dawards',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AwardController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AwardController@index',
        'as' => 'DBasic.awards',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.subfleet' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dfleet/{subfleet_type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_FleetController@subfleet',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_FleetController@subfleet',
        'as' => 'DBasic.subfleet',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.aircraft' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'daircraft/{ac_reg}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_FleetController@aircraft',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_FleetController@aircraft',
        'as' => 'DBasic.aircraft',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.hubs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dhubs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_HubController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_HubController@index',
        'as' => 'DBasic.hubs',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.hub' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dhubs/{icao}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_HubController@show',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_HubController@show',
        'as' => 'DBasic.hub',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.news' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dnews',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_NewsController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_NewsController@index',
        'as' => 'DBasic.news',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.ranks' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dranks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_RankController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_RankController@index',
        'as' => 'DBasic.ranks',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.roster' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'droster',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_RosterController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_RosterController@index',
        'as' => 'DBasic.roster',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.livewx' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dlivewx',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_PageController@livewx',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_PageController@livewx',
        'as' => 'DBasic.livewx',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.pireps' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dpireps',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_PirepController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_PirepController@index',
        'as' => 'DBasic.pireps',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.scenery' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dscenery',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SceneryController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SceneryController@index',
        'as' => 'DBasic.scenery',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.scenery.flights' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dflights',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SceneryController@flights',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SceneryController@flights',
        'as' => 'DBasic.scenery.flights',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.scenery.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dscenery/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SceneryController@store',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SceneryController@store',
        'as' => 'DBasic.scenery.store',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.scenery.delete' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dscenery/delete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SceneryController@delete',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SceneryController@delete',
        'as' => 'DBasic.scenery.delete',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.stable' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dstable',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_StableApproachController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_StableApproachController@index',
        'as' => 'DBasic.stable',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dstats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_StatisticController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_StatisticController@index',
        'as' => 'DBasic.stats',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.jumpseat' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'djumpseat',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WidgetController@jumpseat',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WidgetController@jumpseat',
        'as' => 'DBasic.jumpseat',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.transferac' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'dtransferac',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WidgetController@transferac',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WidgetController@transferac',
        'as' => 'DBasic.transferac',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.fleet' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dfleet',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_FleetController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_FleetController@index',
        'as' => 'DBasic.fleet',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.reports' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dreports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_PirepController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_PirepController@index',
        'as' => 'DBasic.reports',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.statistics' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dstatistics',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_StatisticController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_StatisticController@index',
        'as' => 'DBasic.statistics',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.vatsim' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dvatsim',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AuditController@vatsim',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AuditController@vatsim',
        'as' => 'DBasic.vatsim',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.ivao' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'divao',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AuditController@ivao',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AuditController@ivao',
        'as' => 'DBasic.ivao',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.audit.export' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'daudit_export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AuditController@export_pireps',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AuditController@export_pireps',
        'as' => 'DBasic.audit.export',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.dp_roster' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dp_roster',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WebController@roster',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WebController@roster',
        'as' => 'DBasic.dp_roster',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.dp_stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dp_stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WebController@stats',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WebController@stats',
        'as' => 'DBasic.dp_stats',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.dp_page' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dp_page',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WebController@page',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WebController@page',
        'as' => 'DBasic.dp_page',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.dp_pireps' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dp_pireps',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WebController@pireps',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_WebController@pireps',
        'as' => 'DBasic.dp_pireps',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dstable/new',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_StableApproachController@store',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_StableApproachController@store',
        'as' => 'DBasic.',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.generated::G6Z1H04ib8xM1I1J' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dbapi/events',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@events',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@events',
        'as' => 'DBasic.generated::G6Z1H04ib8xM1I1J',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.generated::FrTb8qprrTyYm1QM' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dbapi/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@modules',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@modules',
        'as' => 'DBasic.generated::FrTb8qprrTyYm1QM',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.generated::we4ZH5p3jkamnvzA' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dbapi/news',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@news',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@news',
        'as' => 'DBasic.generated::we4ZH5p3jkamnvzA',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.generated::bz6xEKOOjXc2EpGq' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dbapi/pireps',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@pireps',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@pireps',
        'as' => 'DBasic.generated::bz6xEKOOjXc2EpGq',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.generated::kt1sCxP8HNJ08NGm' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dbapi/roster',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@roster',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@roster',
        'as' => 'DBasic.generated::kt1sCxP8HNJ08NGm',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.generated::cIq4Mgq5jLaHfUW0' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dbapi/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@stats',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_ApiController@stats',
        'as' => 'DBasic.generated::cIq4Mgq5jLaHfUW0',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.admin' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dbasic',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@index',
        'as' => 'DBasic.admin',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.health_check' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dcheck',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@health_check',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@health_check',
        'as' => 'DBasic.health_check',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.park_aircraft' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dpark_aircraft',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@park_aircraft',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@park_aircraft',
        'as' => 'DBasic.park_aircraft',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.specs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dspecs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SpecController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SpecController@index',
        'as' => 'DBasic.specs',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.specs_store' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dspecs_store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SpecController@store',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_SpecController@store',
        'as' => 'DBasic.specs_store',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.tech' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dtech',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_TechController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_TechController@index',
        'as' => 'DBasic.tech',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.tech_store' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/dtech_store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_TechController@store',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_TechController@store',
        'as' => 'DBasic.tech_store',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.runway' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/drunway',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_RunwayController@index',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_RunwayController@index',
        'as' => 'DBasic.runway',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.runway_store' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'admin/drunway_store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_RunwayController@store',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_RunwayController@store',
        'as' => 'DBasic.runway_store',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.stable_update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/dstable/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_StableApproachController@update',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_StableApproachController@update',
        'as' => 'DBasic.stable_update',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.manual_award' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/dmanual_award',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@manual_award',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@manual_award',
        'as' => 'DBasic.manual_award',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DBasic.manual_payment' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/dmanual_payment',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin,addons,modules',
        ),
        'uses' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@manual_payment',
        'controller' => 'Modules\\DisposableBasic\\Http\\Controllers\\DB_AdminController@manual_payment',
        'as' => 'DBasic.manual_payment',
        'namespace' => 'Modules\\DisposableBasic\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.assignments' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dassignments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_AssignmentController@index',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_AssignmentController@index',
        'as' => 'DSpecial.assignments',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.freeflight' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dfreeflight',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_FreeFlightController@index',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_FreeFlightController@index',
        'as' => 'DSpecial.freeflight',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.freeflight_store' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'dfreeflight_store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_FreeFlightController@store',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_FreeFlightController@store',
        'as' => 'DSpecial.freeflight_store',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.maintenance' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dmaintenance',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MaintenanceController@index',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MaintenanceController@index',
        'as' => 'DSpecial.maintenance',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.market' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dmarket',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MarketController@index',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MarketController@index',
        'as' => 'DSpecial.market',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.market.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dmarket/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MarketController@show',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MarketController@show',
        'as' => 'DSpecial.market.show',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.market.buy' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dmarket/buy',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MarketController@buy',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MarketController@buy',
        'as' => 'DSpecial.market.buy',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.missions' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dmissions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MissionController@index',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MissionController@index',
        'as' => 'DSpecial.missions',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.missions.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'dmissions/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MissionController@store',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MissionController@store',
        'as' => 'DSpecial.missions.store',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.notams' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dnotams',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_NotamController@index',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_NotamController@index',
        'as' => 'DSpecial.notams',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.ops_manual' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dopsmanual',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_PageController@ops_manual',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_PageController@ops_manual',
        'as' => 'DSpecial.ops_manual',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.landing_rates' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dlandingrates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_PageController@landing_rates',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_PageController@landing_rates',
        'as' => 'DSpecial.landing_rates',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.tours' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dtours',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@index',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@index',
        'as' => 'DSpecial.tours',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.tour' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dtours/{code}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@show',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@show',
        'as' => 'DSpecial.tour',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.about_us' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'daboutus',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_PageController@about_us',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_PageController@about_us',
        'as' => 'DSpecial.about_us',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.rules_regs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'drulesandregs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_PageController@rules_regs',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_PageController@rules_regs',
        'as' => 'DSpecial.rules_regs',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dsapi/assignments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_ApiController@assignments',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_ApiController@assignments',
        'as' => 'DSpecial.',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.generated::ywCl7bNJngURzD4i' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dsapi/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_ApiController@modules',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_ApiController@modules',
        'as' => 'DSpecial.generated::ywCl7bNJngURzD4i',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.generated::DvHteDDNULDlOYMl' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dsapi/tours',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_ApiController@tours',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_ApiController@tours',
        'as' => 'DSpecial.generated::DvHteDDNULDlOYMl',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.admin' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dspecial',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_AdminController@index',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_AdminController@index',
        'as' => 'DSpecial.admin',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.save_settings' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/dsettings_store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_AdminController@update',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_AdminController@update',
        'as' => 'DSpecial.save_settings',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.assignments_manual' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/dassignments_manual',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,users|flights',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_AssignmentController@assignments_manual',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_AssignmentController@assignments_manual',
        'as' => 'DSpecial.assignments_manual',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.market_admin' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dmarket_admin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MarketController@index_admin',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MarketController@index_admin',
        'as' => 'DSpecial.market_admin',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.market_store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/dmarket_store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MarketController@store',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MarketController@store',
        'as' => 'DSpecial.market_store',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.maint_admin' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dmaint_admin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MaintenanceController@index_admin',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MaintenanceController@index_admin',
        'as' => 'DSpecial.maint_admin',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.maint_finish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/dmaint_finish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MaintenanceController@finish_maint',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_MaintenanceController@finish_maint',
        'as' => 'DSpecial.maint_finish',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.notam_admin' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dnotam_admin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_NotamController@index_admin',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_NotamController@index_admin',
        'as' => 'DSpecial.notam_admin',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.notam_store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/dnotam_store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_NotamController@store',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_NotamController@store',
        'as' => 'DSpecial.notam_store',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.tour_admin' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dtour_admin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@index_admin',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@index_admin',
        'as' => 'DSpecial.tour_admin',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.tour_store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/dtour_store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@store',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@store',
        'as' => 'DSpecial.tour_store',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.tour_legactions' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/dtour_legactions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@leg_actions',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@leg_actions',
        'as' => 'DSpecial.tour_legactions',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'DSpecial.tour_remove' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dtours/remove/{pirep_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'ability:admin,admin-access',
          3 => 'ability:admin|admin-access,addons|modules|pireps',
        ),
        'uses' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@remove_from_pirep',
        'controller' => 'Modules\\DisposableSpecial\\Http\\Controllers\\DS_TourController@remove_from_pirep',
        'as' => 'DSpecial.tour_remove',
        'namespace' => 'Modules\\DisposableSpecial\\Http\\Controllers',
        'prefix' => 'admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'passport',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth',
        ),
        'uses' => 'Modules\\SPPassport\\Http\\Controllers\\Frontend\\IndexController@index',
        'controller' => 'Modules\\SPPassport\\Http\\Controllers\\Frontend\\IndexController@index',
        'as' => 'passport.index',
        'namespace' => 'Modules\\SPPassport\\Http\\Controllers\\Frontend',
        'prefix' => 'passport',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.compare' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'passport/compare/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth',
        ),
        'uses' => 'Modules\\SPPassport\\Http\\Controllers\\Frontend\\CompareController@show',
        'controller' => 'Modules\\SPPassport\\Http\\Controllers\\Frontend\\CompareController@show',
        'as' => 'passport.compare',
        'namespace' => 'Modules\\SPPassport\\Http\\Controllers\\Frontend',
        'prefix' => 'passport',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'user' => '[0-9]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.flights.country' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'passport/flights/{country}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth',
        ),
        'uses' => 'Modules\\SPPassport\\Http\\Controllers\\Frontend\\FlightSearchController@searchByCountry',
        'controller' => 'Modules\\SPPassport\\Http\\Controllers\\Frontend\\FlightSearchController@searchByCountry',
        'as' => 'passport.flights.country',
        'namespace' => 'Modules\\SPPassport\\Http\\Controllers\\Frontend',
        'prefix' => 'passport',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'country' => '[a-zA-Z]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.passport.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/passport',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\SPPassport\\Http\\Controllers\\Admin\\AdminController@index',
        'controller' => 'Modules\\SPPassport\\Http\\Controllers\\Admin\\AdminController@index',
        'as' => 'admin.passport.',
        'namespace' => 'Modules\\SPPassport\\Http\\Controllers\\Admin',
        'prefix' => 'admin/passport',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.passport.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/passport',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\SPPassport\\Http\\Controllers\\Api\\ApiController@index',
        'controller' => 'Modules\\SPPassport\\Http\\Controllers\\Api\\ApiController@index',
        'as' => 'api.passport.',
        'namespace' => 'Modules\\SPPassport\\Http\\Controllers\\Api',
        'prefix' => 'api/passport',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.passport.generated::2zudDgnfDyauhuDL' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/passport/hello',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'Modules\\SPPassport\\Http\\Controllers\\Api\\ApiController@hello',
        'controller' => 'Modules\\SPPassport\\Http\\Controllers\\Api\\ApiController@hello',
        'as' => 'api.passport.generated::2zudDgnfDyauhuDL',
        'namespace' => 'Modules\\SPPassport\\Http\\Controllers\\Api',
        'prefix' => 'api/passport',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sptheme.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sptheme',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Modules\\SPTheme\\Http\\Controllers\\Frontend\\IndexController@index',
        'controller' => 'Modules\\SPTheme\\Http\\Controllers\\Frontend\\IndexController@index',
        'as' => 'sptheme.',
        'namespace' => 'Modules\\SPTheme\\Http\\Controllers\\Frontend',
        'prefix' => 'sptheme',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.sptheme.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/sptheme',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\SPTheme\\Http\\Controllers\\Admin\\AdminController@index',
        'controller' => 'Modules\\SPTheme\\Http\\Controllers\\Admin\\AdminController@index',
        'as' => 'admin.sptheme.',
        'namespace' => 'Modules\\SPTheme\\Http\\Controllers\\Admin',
        'prefix' => 'admin/sptheme',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.sptheme.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/sptheme/sptheme',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\SPTheme\\Http\\Controllers\\Admin\\AdminController@index',
        'controller' => 'Modules\\SPTheme\\Http\\Controllers\\Admin\\AdminController@index',
        'as' => 'admin.sptheme.index',
        'namespace' => 'Modules\\SPTheme\\Http\\Controllers\\Admin',
        'prefix' => 'admin/sptheme/sptheme',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.sptheme.storeSettings' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/sptheme/sptheme/updateSettings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\SPTheme\\Http\\Controllers\\Admin\\AdminController@storeSettings',
        'controller' => 'Modules\\SPTheme\\Http\\Controllers\\Admin\\AdminController@storeSettings',
        'as' => 'admin.sptheme.storeSettings',
        'namespace' => 'Modules\\SPTheme\\Http\\Controllers\\Admin',
        'prefix' => 'admin/sptheme/sptheme',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.sptheme.storeCrewtest' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/sptheme/sptheme/updateCrewtest',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\SPTheme\\Http\\Controllers\\Admin\\AdminController@storeCrewtest',
        'controller' => 'Modules\\SPTheme\\Http\\Controllers\\Admin\\AdminController@storeCrewtest',
        'as' => 'admin.sptheme.storeCrewtest',
        'namespace' => 'Modules\\SPTheme\\Http\\Controllers\\Admin',
        'prefix' => 'admin/sptheme/sptheme',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.sptheme.resetSettings' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/sptheme/sptheme/resetSettings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\SPTheme\\Http\\Controllers\\Admin\\AdminController@resetSettings',
        'controller' => 'Modules\\SPTheme\\Http\\Controllers\\Admin\\AdminController@resetSettings',
        'as' => 'admin.sptheme.resetSettings',
        'namespace' => 'Modules\\SPTheme\\Http\\Controllers\\Admin',
        'prefix' => 'admin/sptheme/sptheme',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.sptheme.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/sptheme',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\SPTheme\\Http\\Controllers\\Api\\ApiController@index',
        'controller' => 'Modules\\SPTheme\\Http\\Controllers\\Api\\ApiController@index',
        'as' => 'api.sptheme.',
        'namespace' => 'Modules\\SPTheme\\Http\\Controllers\\Api',
        'prefix' => 'api/sptheme',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.sptheme.generated::FQuIeUrxyNLwQ7h5' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/sptheme/hello',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'Modules\\SPTheme\\Http\\Controllers\\Api\\ApiController@hello',
        'controller' => 'Modules\\SPTheme\\Http\\Controllers\\Api\\ApiController@hello',
        'as' => 'api.sptheme.generated::FQuIeUrxyNLwQ7h5',
        'namespace' => 'Modules\\SPTheme\\Http\\Controllers\\Api',
        'prefix' => 'api/sptheme',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sptransfer.hub.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sptransfer/hub',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend\\HubFrontendController@index',
        'controller' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend\\HubFrontendController@index',
        'as' => 'sptransfer.hub.index',
        'namespace' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend',
        'prefix' => '/sptransfer',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sptransfer.hub.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'sptransfer/hub',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend\\HubFrontendController@store',
        'controller' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend\\HubFrontendController@store',
        'as' => 'sptransfer.hub.store',
        'namespace' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend',
        'prefix' => '/sptransfer',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sptransfer.airline.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sptransfer/airline',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend\\AirlineFrontendController@index',
        'controller' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend\\AirlineFrontendController@index',
        'as' => 'sptransfer.airline.index',
        'namespace' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend',
        'prefix' => '/sptransfer',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sptransfer.airline.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'sptransfer/airline',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend\\AirlineFrontendController@store',
        'controller' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend\\AirlineFrontendController@store',
        'as' => 'sptransfer.airline.store',
        'namespace' => 'Modules\\SPTransfer\\Http\\Controllers\\Frontend',
        'prefix' => '/sptransfer',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.sptransfer.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/sptransfer',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin\\AdminController@index',
        'controller' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin\\AdminController@index',
        'as' => 'admin.sptransfer.index',
        'namespace' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin',
        'prefix' => 'admin/sptransfer',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.sptransfer.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/sptransfer/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin\\AdminController@update',
        'controller' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin\\AdminController@update',
        'as' => 'admin.sptransfer.update',
        'namespace' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin',
        'prefix' => 'admin/sptransfer',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.sptransfer.update_airline' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/sptransfer/update_airline',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin\\AdminController@update_airline',
        'controller' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin\\AdminController@update_airline',
        'as' => 'admin.sptransfer.update_airline',
        'namespace' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin',
        'prefix' => 'admin/sptransfer',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.sptransfer.storeSettings' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/sptransfer/storeSettings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin\\AdminController@storeSettings',
        'controller' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin\\AdminController@storeSettings',
        'as' => 'admin.sptransfer.storeSettings',
        'namespace' => 'Modules\\SPTransfer\\Http\\Controllers\\Admin',
        'prefix' => 'admin/sptransfer',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sample.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sample',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:user',
        ),
        'uses' => 'Modules\\Sample\\Http\\Controllers\\SampleController@index',
        'controller' => 'Modules\\Sample\\Http\\Controllers\\SampleController@index',
        'as' => 'sample.',
        'namespace' => 'Modules\\Sample\\Http\\Controllers',
        'prefix' => 'sample',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sample.generated::QrQVG5GJbl3Xb6Cd' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/sample',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\Sample\\Http\\Controllers\\Admin\\AdminController@index',
        'controller' => 'Modules\\Sample\\Http\\Controllers\\Admin\\AdminController@index',
        'as' => 'sample.generated::QrQVG5GJbl3Xb6Cd',
        'namespace' => 'Modules\\Sample\\Http\\Controllers\\Admin',
        'prefix' => 'admin/sample',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sample.generated::tKrbZBNLMoT9QMTE' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/sample/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => 'Modules\\Sample\\Http\\Controllers\\Admin\\AdminController@create',
        'controller' => 'Modules\\Sample\\Http\\Controllers\\Admin\\AdminController@create',
        'as' => 'sample.generated::tKrbZBNLMoT9QMTE',
        'namespace' => 'Modules\\Sample\\Http\\Controllers\\Admin',
        'prefix' => 'admin/sample',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sample.generated::H6O1HRHHa5hk1axr' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/sample',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Modules\\Sample\\Http\\Controllers\\Api\\SampleController@index',
        'controller' => 'Modules\\Sample\\Http\\Controllers\\Api\\SampleController@index',
        'as' => 'sample.generated::H6O1HRHHa5hk1axr',
        'namespace' => 'Modules\\Sample\\Http\\Controllers\\Api',
        'prefix' => 'api/sample',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sample.generated::ApLqx5X9Ri4apaQ5' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/sample/hello',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => 'Modules\\Sample\\Http\\Controllers\\Api\\SampleController@hello',
        'controller' => 'Modules\\Sample\\Http\\Controllers\\Api\\SampleController@hello',
        'as' => 'sample.generated::ApLqx5X9Ri4apaQ5',
        'namespace' => 'Modules\\Sample\\Http\\Controllers\\Api',
        'prefix' => 'api/sample',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'vmsacars.admin.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/vmsacars',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin\\AdminController@index',
        'controller' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin\\AdminController@index',
        'as' => 'vmsacars.admin.index',
        'namespace' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin',
        'prefix' => 'admin/vmsacars',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'vmsacars.admin.config' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/vmsacars/config',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin\\AdminController@config',
        'controller' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin\\AdminController@config',
        'as' => 'vmsacars.admin.config',
        'namespace' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin',
        'prefix' => 'admin/vmsacars',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'vmsacars.admin.rules' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/vmsacars/rules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin\\AdminController@rules',
        'controller' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin\\AdminController@rules',
        'as' => 'vmsacars.admin.rules',
        'namespace' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin',
        'prefix' => 'admin/vmsacars',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'vmsacars.admin.broadcast' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/vmsacars/broadcast',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'role:admin',
        ),
        'uses' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin\\AdminController@broadcast',
        'controller' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin\\AdminController@broadcast',
        'as' => 'vmsacars.admin.broadcast',
        'namespace' => '\\Modules\\VMSAcars\\Http\\Controllers\\Admin',
        'prefix' => 'admin/vmsacars',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'vmsacars.api.vmsacars.api.config' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/vmsacars/config',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => '\\Modules\\VMSAcars\\Http\\Controllers\\Api\\ApiController@config',
        'controller' => '\\Modules\\VMSAcars\\Http\\Controllers\\Api\\ApiController@config',
        'as' => 'vmsacars.api.vmsacars.api.config',
        'namespace' => '\\Modules\\VMSAcars\\Http\\Controllers\\Api',
        'prefix' => 'api/vmsacars',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'vmsacars.api.vmsacars.api.search' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/vmsacars/search',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => '\\Modules\\VMSAcars\\Http\\Controllers\\Api\\ApiController@search',
        'controller' => '\\Modules\\VMSAcars\\Http\\Controllers\\Api\\ApiController@search',
        'as' => 'vmsacars.api.vmsacars.api.search',
        'namespace' => '\\Modules\\VMSAcars\\Http\\Controllers\\Api',
        'prefix' => 'api/vmsacars',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'vmsacars.api.rules' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/vmsacars/rules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'api.auth',
        ),
        'uses' => '\\Modules\\VMSAcars\\Http\\Controllers\\Api\\ApiController@rules',
        'controller' => '\\Modules\\VMSAcars\\Http\\Controllers\\Api\\ApiController@rules',
        'as' => 'vmsacars.api.rules',
        'namespace' => '\\Modules\\VMSAcars\\Http\\Controllers\\Api',
        'prefix' => 'api/vmsacars',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);

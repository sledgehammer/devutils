app.config(function ($stateProvider, $urlRouterProvider) {
    $stateProvider.state('home', {
        url: '/',
        templateUrl: 'components/package/packagelist.html',
        controller: 'PackagesController',
         
    });
    $urlRouterProvider.otherwise('/');
});

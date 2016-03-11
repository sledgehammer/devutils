
app.controller('PackagesController', function(commands) {
    commands.register({ title: 'phpinfo()', command: 'url', args: '/phpinfo' });
});
app.factory('commands', function () {
    var commands = [
        {title: 'Package "sledgehammer/core"', command: 'package', args: 'sledgehammer/core'},
        {title: 'Package "sledgehammer/mvc"', command: 'package', args: 'sledgehammer/mvc'},
        {title: 'Package "sledgehammer/orm"', command: 'package', args: 'sledgehammer/orm'},
        {title: 'Package "sledgehammer/wordpress"', command: 'package', args: 'sledgehammer/wordpress'},
        {title: 'Package "noprotorocol/topgear"', command: 'package', args: 'noprotorocol/topgear'},
    ];
    
    var subject = new Rx.BehaviorSubject(commands);
    return {
        list: subject.asObservable(),
        register: function (command) {
            subject.next([...commands, command]);
        }
    };
});
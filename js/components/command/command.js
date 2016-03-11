/**
 * The command pallete.
 * 
 * Keyboard driver navigation, based on sublime-text's "Command pallete"
 * - magic focus: Just start typing and the command__input automaticly gets the focus.
 */
app.component('duCommand', {
    templateUrl: 'components/command/command.html',
    controllerAs: 'vm',
    controller: function ($element, $scope, filterFilter, commands) {
        var vm = this;
        var input = $element.find('.command__input');
        var KEYS = {
            ENTER: 13,
            UP: 38,
            DOWN: 40,
            ESC: 27
        }
        var operations  = [];
        commands.list.subscribe(function (_operations) {
            operations = _operations;
        });

        input.keydown(function (e) {
            if (e.which === KEYS.UP || e.which === KEYS.DOWN) {
                if (vm.commands.length) {
                    e.preventDefault();
                    var index = vm.commands.indexOf(vm.active);
                    if (e.which === KEYS.UP) {
                        index--;
                        if (index < 0) {
                            index = vm.commands.length - 1;
                        }
                    } else {
                        index++;
                        if (index >= vm.commands.length) {
                            index = 0;
                        }
                    }
                    vm.active = vm.commands[index];
                }
                $scope.$digest();
            }
            if (e.which === KEYS.ESC) {
                input.blur();
            }
        });
        input.on('input', function (e) {
            if (setQuery(input.val())) {
                $scope.$digest();
            }
        });
        var query = false;
        function setQuery(value) {
            if (value === query) {
                return false;
            }
            query = value;
            if (query === '') {
                vm.commands = operations;
            } else {
                vm.commands = filterFilter(operations, query);
            }
            vm.active = vm.commands[0];
            return true;
        }
        setQuery('')
        input.focus(function () {
            vm.focussed = true;
            $scope.$digest();
        });
        input.blur(function () {
            vm.focussed = false;
            $scope.$digest();
        });
        $('body').keypress(function (e) {
            if (e.altKey || e.ctrlKey || e.metaKey) { // control/alt or meta(command/window) key was also pressed?
                return; // ignore event
            }
            if ($(e.target).is('textarea,input,select')) { // inside an input?
                return;
            }
            var key = String.fromCharCode(e.keyCode);
            input.val('');
            console.log();
            setQuery(key);
            input.focus();
        });
    }
}).animation('.command__result', function () {
    return {
        addClass: function(el, className, done) {
            if (className !== 'command__result--active') {
                done();
                return;
            }
            var parent = el[0].parentElement;
            var offsetTop = (el[0].offsetTop  - parent.scrollTop);
            if (offsetTop < 0) {
                $(parent).animate({scrollTop: '+=' + offsetTop}, 100, done);
            }
            var offsetBottom = (el[0].offsetTop + el[0].offsetHeight) - (parent.clientHeight + parent.scrollTop);
            if (offsetBottom > 0) {
                $(parent).animate({scrollTop: '+=' + offsetBottom}, 100, done);
            }
            return function () {
                $(parent).stop(false, true);
            }
        } 
    };
});

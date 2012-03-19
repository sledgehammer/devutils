#!/usr/bin/expect
# Usage: login.sh username password command

set username [lindex $argv 0]
set password [lindex $argv 1]
set command [lindex $argv 2]
set env(PS1) "# "
spawn su $username
expect "Password:" { send "$password\r" }
expect "# " { send "$command\r" }
expect "# " { send "q" }
exit
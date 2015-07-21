if [ "$scriptDir" = "" ]; then
	# Figure out where the current script lives.  Run from that directory.
	scriptDir="`dirname \"$0\"`"
	[ "$scriptDir" != "" ] && cd "$scriptDir"
	scriptDir="`pwd`"
fi

# Clear old ini_* environment variables.
old_ini_vars="`set|grep \"^ini_\" | cut -f1 -d\"=\"`"
for varname in $old_ini_vars; do
	unset "$varname"
done

tmpfile=/tmp/tmpdbcfg$$
php "$scriptDir/../phpdaogen/parseini_multi.php" "$scriptDir/../config/database.ini" "$scriptDir/../ddl" "$connectionName" > "$tmpfile"
retval="$?"
if [ "$retval" -ne "0" ]; then
	rm -f "$tmpfile"
	exit "$retval"
fi
. "$tmpfile"
rm -f "$tmpfile"

case "$ini_connectionClass" in
MySQLConnection|MySQLiConnection)
	dialect=mysql
	pipecmd="mysql -h \"$ini_server\" --user=\"$ini_username\" --password=\"$ini_password\" \"$ini_database\" --verbose -f"
	;;
PostgreSQLConnection)
	dialect=pgsql
	pipecmd="psql -h \"$ini_server\" -U \"$ini_username\" \"$ini_database\""
### /// TODO: If possible, figure out a way to pass the password in on the command line, or figure out a way to make it so that certain login users have unlimited access to postgresql (ident authentication).
### --password=\"$ini_password\"
	;;
*)
	echo "Unsupported connection class: $ini_connectionClass" >&2
	exit 1;
	;;
esac

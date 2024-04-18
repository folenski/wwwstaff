# Test  API
# api/auth
host="localhost:5000"
Bearer="Authorization: Bearer ODExYzFkNzZmNDc4MjkzNmEyZWQwY2E5ZDM4N2JhMmZhNWE0MTU2MTAxNmQ4ZGFkOTk4OTUzNWRmMjZjOTMwYg=="
Json="Content-Type: application/json"

# Autorisation
curl -i -X POST -H "${Json}" -d '{"user":"admin", "password":"admin"}' "http://${host}/api/auth"
curl -i -X POST -H "${Json}" -d '{"user":"admin", "password":"admin2"}' "http://${host}/api/auth"
curl -i -X DELETE -H "${Json}" -H "${Bearer}" "http://${host}/api/auth"

# utilisateurs
curl -i -X GET -H "${Json}" -H "${Bearer}" "http://${host}/api/user" 
curl -i -X POST -H "${Json}" -H "${Bearer}" -d '{"user":"mario","password":"123","mail":"m@m.f","group":"user"}' "http://${host}/api/user"
curl -i -X PUT -H "${Json}" -H "${Bearer}" -d '{"mail":"mario@mario.f"}' "http://${host}/api/user/mario"
curl -i -X DELETE -H "${Json}" -H "${Bearer}" "http://${host}/api/user/mario" 

# messages
curl -i -X GET  -H "${Bearer}" "http://${host}/api/msg" 
curl -i -X PUT -H "${Json}" -H "${Bearer}" -d '{"read":true}' "http://${host}/api/msg/1"
curl -i -X DELETE -H "${Json}" -H "${Bearer}" "http://${host}/api/msg/1" 

# logs
curl -i -X GET  -H "${Bearer}" "http://${host}/api/log" 
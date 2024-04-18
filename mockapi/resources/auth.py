""" 
Class Auth
Permet de gerer l'authentification d'un utilisateur

date: 05/08/2023
- Mise à jour de la propriété valid
"""

from flask_restful import Resource, reqparse
from config import Data


class Auth(Resource):

    def __init__(self):
        self.users = Data.load_users()

    def get(self): return {}, 404

    def post(self):
        parser = reqparse.RequestParser()
        parser.add_argument("user", required=True)
        parser.add_argument("password", required=True)
        args = parser.parse_args()

        user = Data.find_user(self.users, args["user"])
        if user is not None and args["password"] == user["password"]:
            return {"token": user["token"], "mail": user["mail"], "last": user["last"],
                    "until": user["until"]}, 201
        return {"errorcode": 44, "message": "user or password is not valid"}, 200

    def put(self): return {}, 404

    def delete(self):
        if Data.is_allowed(self.users):
            return {"message": "done"}, 200
        else:
            return {"message": "bad token"}, 401
    
    def options(self):
        return {"allow": "AUTH"}, 200

""" 
Class User
Permet de gérer les utilisateurs

date: 23/10/2022
"""
from datetime import datetime
from flask_restful import Resource, reqparse
from config import Data


USERS = Data.load_users()


class User(Resource):

    def get(self, user=None):
        """
        Liste l'utilisateur ou les utilisateurs, accès avec bearer
        """
        list_fields = ("name", "mail", "admin", "last")
        if not Data.is_allowed(USERS):
            return {"content": "bad token"}, 401
        if user is None:
            return Data.filter_users(USERS, list_fields), 200
        user = Data.find_user(USERS, user)
        if user is None:
            return {"http": 204, "content": "not found"}
        return Data.filter_users([user], list_fields), 200

    def post(self):
        """
        Ajoute un utilisateur, accès avec bearer
        """
        if not Data.is_allowed(USERS):
            return {"content": "bad token"}, 401
        now = datetime.now().strftime('%Y-%m-%dT%H:%M:%S.%f')
        parser = reqparse.RequestParser()
        parser.add_argument("name", required=True)
        parser.add_argument("password", required=True)
        parser.add_argument("mail", required=True)
        parser.add_argument("group")
        args = parser.parse_args()
        if Data.find_user(USERS, args["user"]) is not None:
            return {"content": "user exist"}, 200
        if 'group' in args and args['group'] == "admin":
            group = True
        else:
            group = False
        USERS.append(
            {
                "name": args["name"],
                "mail": args["mail"],
                "password": args["password"],
                "admin": group,
                "last": now,
            }
        )
        return {"content": "success"}, 201

    def put(self, user=None):
        """
        met à jour un utilisateur, accès avec bearer
        """
        if not Data.is_allowed(USERS):
            return {"content": "bad token"}, 401
        if user is None:
            return {"content": "parameter user is missing"}, 401
        now = datetime.now().strftime('%Y-%m-%dT%H:%M:%S.%f')
        parser = reqparse.RequestParser()
        parser.add_argument("password")
        parser.add_argument("mail")
        args = parser.parse_args()
        upd_user = Data.find_user(USERS, user)
        if upd_user is None:
            return {"content": "user doesn't exist"}, 200
        if args['mail'] != 'null':
            upd_user['mail'] = args['mail']
        if args['password'] != 'null':
            upd_user['password'] = args['password']
        upd_user['last'] = now
        Data.remove_user(USERS, upd_user['name'])
        USERS.append(upd_user)
        return {"content": "done"}, 200

    def delete(self, user=None):
        """
        supprime un utilisateur, accès avec bearer
        """
        if not Data.is_allowed(USERS):
            return {"content": "bad token"}, 401
        if user is None:
            return {"content": "parameter user is missing"}, 401
        Data.remove_user(USERS, user)
        return {"content": "done"}, 200


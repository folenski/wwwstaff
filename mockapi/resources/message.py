""" 
Class Message
Mock pour tester les messages pour angular 
@date : 13/08/2023
"""
from flask_restful import Resource, reqparse
from config import Data


MESSAGES = Data.load_messages()


class Message(Resource):
    def __init__(self) -> None:
        self.users = Data.load_users()

    def get(self, id=None):
        """
        Liste tous les messages
        """
        if not Data.is_allowed(self.users):
            return {"message": "bad credentials"}, 401
        return sorted(MESSAGES, key=lambda msg: msg["id"], reverse=True), 200

    def put(self, id=None):
        """
        Met à jour l'attribut read d'un message identifié par son ID
        """
        if not Data.is_allowed(self.users):
            return {"message": "bad credentials"}, 401

        parser = reqparse.RequestParser()
        parser.add_argument("read")
        args = parser.parse_args()

        if id is None:
            return {"errorcode": 30, "message": "id"}, 400

        for msg in MESSAGES:
            if msg["id"] == id:
                msg["read"] = args["read"] == "True"
                break

        return {"message": "done"}, 201

    def delete(self, id=None):
        """
        Supprime un message identifié par son ID
        """
        if not Data.is_allowed(self.users):
            return {"message": "bad credentials"}, 401
        if id is None:
            return {"errorcode": 30, "message": "id"}, 400

        rm_idx = -1
        for index, msg in enumerate(MESSAGES):
            if msg["id"] == id:
                rm_idx = index
                break
        if rm_idx != -1:
            MESSAGES.pop(rm_idx)

        return {"message": "done"}, 200

""" 
Class Log
Mock pour simuler la lecture des logs pour angular 
__date__ : 02/11/2022
"""
from flask_restful import Resource
from config import Data


class Log(Resource):
    def __init__(self) -> None:
        self.logs = sorted(Data.load_logs(), key=lambda log: log["id"], reverse=True)
        self.users = Data.load_users()

    def get(self, id=None):
        """
        Liste toutes les logs
        """
        if not Data.is_allowed(self.users):
            return {"message": "bad credentials"}, 401
        return self.logs, 200


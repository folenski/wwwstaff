""" 
Class Ping

__date__ : 25/08/2023
"""
from flask_restful import Resource
import time


class Ping(Resource):


    def get(self):
        """
        Gestion du message ping
        """
        time.sleep(1)
        return {"message": "online"}, 200


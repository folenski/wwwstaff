""" 
Class Environment
Mock pour simuler des utilisateurs pour angular 
@date : 03/11/2022
"""
from flask_restful import Resource, reqparse
from base import environments

class Environment(Resource):
    def get(self, name=None):
        if name is not None:
            for env in environments:
                if env["name"] == name:
                    return env, 200
            return {"errorcode": 30, "content": "environment with id % not exist" % name}, 200
        return environments, 200

    def put(self, name=None):
        parser = reqparse.RequestParser()
        parser.add_argument("template")
        parser.add_argument("file_php")
        parser.add_argument("order_by")
        args = parser.parse_args()

        if name is None:
            return {"errorcode": 30, "content": "template id_div needed"}, 200
        for env in environments:
            if env["name"] == name:
                if args["template"] != "":  env["template"] = args["template"] 
                if args["file_php"] != "":  env["file_php"] = args["file_php"] 
                if args["order_by"] != "":  env["order_by"] = args["order_by"] 
                return {"errorcode": 5, "content": "template updated"}, 200

        return {"errorcode": 30, "content": "template with id_div % not exist" % name}, 200

    def delete(self, name=None):
        if name is None:
            return {"errorcode": 30, "content": "environment name needed"}, 200

        for index in range(0, len(environments)):
            if environments[index]["id_div"] == name:
                environments.pop(index)
                return {"errorcode": 0, "content": "removee env ok"}, 200

        return {"errorcode": 0, "content": "remove env ko"}, 200

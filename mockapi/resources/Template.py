""" 
Class Template
Mock pour simuler des utilisateurs pour angular 
@date : 03/11/2022
"""
from flask_restful import Resource, reqparse
from base import templates

class Template(Resource):
    def get(self, id_div=None):
        if id_div is not None:
            for template in templates:
                if template["id_div"] == id_div:
                    return template, 200
            return {"errorcode": 30, "content": "template with id % not exist" % id_div}, 200
        return templates, 200

    def put(self, id_div=None):
        parser = reqparse.RequestParser()
        parser.add_argument("template")
        parser.add_argument("file_php")
        parser.add_argument("order_by")
        args = parser.parse_args()

        if id_div is None:
            return {"errorcode": 30, "content": "template id_div needed"}, 200
        for template in templates:
            if template["id_div"] == id_div:
                if args["template"] != "":  template["template"] = args["template"] 
                if args["file_php"] != "":  template["file_php"] = args["file_php"] 
                if args["order_by"] != "":  template["order_by"] = args["order_by"] 
                return {"errorcode": 5, "content": "template updated"}, 200

        return {"errorcode": 30, "content": "template with id_div % not exist" % id_div}, 200

    def delete(self, id_div=None):
        if id_div is None:
            return {"errorcode": 30, "content": "template id_div needed"}, 200

        for index in range(0, len(templates)):
            if templates[index]["id_div"] == id_div:
                templates.pop(index)
                return {"errorcode": 0, "content": "remove template ok"}, 200

        return {"errorcode": 0, "content": "remove template ko"}, 200

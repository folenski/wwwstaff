#!/usr/bin/env python3
"""
Permet de simuler les appels API du projet STAFF

date: 05/08/2023 
"""

from flask import Flask
from flask_restful import Api
from resources.auth import Auth
from resources.user import User
from resources.message import Message
from resources.Template import Template
from resources.Log import Log
from Environment import Environment

app = Flask(__name__)
api = Api(app)

print(">>>> Initialisation des ressources Mock REST")
api.add_resource(Auth, "/api/auth")
api.add_resource(User, "/api/user", "/api/user/<user>")
api.add_resource(Log, "/api/log")
api.add_resource(Message, "/api/msg", "/api/msg/<int:id>")
api.add_resource(Template, "/api/tpl", "/api/tpl/<id_div>")
api.add_resource(Environment, "/api/env")
app.run(debug=True)

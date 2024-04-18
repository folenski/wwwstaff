""" 
Class Data
fonctions communes pour les ressources REST du projet

date: 05/08/2023
"""
import json
from flask import request


class Data():

    @staticmethod
    def load_users() -> list:
        """
        le fichier users.json est un tableau d'objets
        retourne le tableau des utilisateurs (data/users.json)
        """
        with open('./data/users.json') as user_file:
            return json.load(user_file)

    @staticmethod
    def load_messages() -> list:
        """
        le fichier messages.json est un tableau d'objets
        retourne le tableau des messages (data/messages.json)
        """
        with open('./data/messages.json') as user_file:
            return json.load(user_file)
        
    @staticmethod
    def load_logs() -> list:
        """
        le fichier logs.json est un tableau d'objets
        retourne le tableau des logs (data/logs.json)
        """
        with open('./data/logs.json') as user_file:
            return json.load(user_file)

    @staticmethod
    def find_user(users: list, name: str) -> dict | None:
        """
        retourne l'utilisateur ou None
        """
        for user in users:
            if "name" not in user:
                continue
            if name == user["name"]:
                return user
        return None

    @staticmethod
    def remove_user(users: list, name: str) -> bool:
        """
        supprime un utilisateur
        la liste users est passée par référence 
        """
        rm_idx = -1
        for idx, user in enumerate(users):
            if "name" not in user:
                continue
            if name == user["name"]:
                rm_idx = idx
                break
        if rm_idx >= 0:
            users.pop(rm_idx)
        return True if rm_idx >= 0 else False

    @staticmethod
    def filter_users(users: list, keep_properties: tuple) -> list:
        """
        filtre le tableau des utilisateurs avec les propriétés données  
        et retourne le tableau filtré
        """
        users_out = []
        for user in users:
            user_mod = user.copy()
            for prop in user.keys():
                if prop not in (keep_properties):
                    user_mod.pop(prop)
            users_out.append(user_mod)
        return users_out

    @staticmethod
    def is_allowed(users: list) -> bool:
        """
        retourne vrai si l'header html possède un token authorisé
        """
        if 'Authorization' not in request.headers:
            return False
        bearer = request.headers['Authorization'].split(' ')[-1]
        for user in users:
            if "token" not in user:
                continue
            if bearer == user["token"]:
                return True
        return False

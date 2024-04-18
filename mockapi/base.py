""" base mock API """
import json

def load_users():
    with open('./config/users.json') as user_file: return json.load(user_file)


def users_exists(name: str):
    index = 0
    for user in users:
        if name == user["name"]:
            return index
        index += 1
    return None


def users2Api(prop):
    users_out = []
    for user in users:
        myuser = user.copy()
        myuser.pop(prop)
        users_out.append(myuser)
    return users_out

templates = [
    {
        "id_div": "menu",
        "template": '<div class="container-fluid"><a class="navbar-brand" href="#">Staff-site demo</a><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button> <div class="collapse navbar-collapse" id="navbarNavDropdown"> <ul class="navbar-nav"> {{#nav}} {{^down}} <li class="nav-item"><a class="nav-link {{active}}" href="{{uri}}">{{name}}</a></li> {{/down}} {{#down}} <li class="nav-item dropdown"><a class="nav-link dropdown-toggle {{active}}" href="{{uri}}" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{name}}</a><ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">{{#dropdown}}<li><a class="dropdown-item" href="{{uri}}">{{name}}</a></li>{{/dropdown}}</ul></li>{{/down}}{{/nav}}</ul></div></div>',
        "file_php": "menu.php",
        "order_by": 0,
        "created_at": "2022-08-16 21:56:07",
        "updated_at": "2022-08-18 20:50:07",
    },
    {
        "id_div": "content",
        "template": "{{#content}}<section>{{section}}</section>{{/content}}",
        "file_php": "content.php",
        "order_by": 0,
        "created_at": "2022-08-16 21:56:07",
        "updated_at": "2022-08-18 20:50:07",
    },
    {
        "id_div": "news",
        "template": '{{#content}}\n<div class="row shadow-sm p-3 my-3 bg-body rounded"> <div class="col-4"> <img class="img-fluid" src="{{img_src}}" alt="{{img_alt}}" data-reveal="display" /> </div> <div class="col-8"> <h3>{{title}}</h3> <hr /> <h4>{{date}}</h4> {{article}} </div> </div> {{/content}}',
        "file_php": "news.php",
        "order_by": 0,
        "created_at": "2022-08-16 21:56:07",
        "updated_at": "2022-08-18 20:50:07",
    },
]

environments = [
    {
        "name": "DEV",
        "j_option": {
            "log": True,
            "prod": False,
            "user": "svcstaff",
            "maintenance": False,
            "pref_uri": "/menu/",
        },
        "j_contact": {
            "mail": "sample@mail.com",
            "msgMax": 2000,
            "sendMail": False,
            "msgOk": "Message envoyé, merci",
            "msgKo": "Une erreur a été rencontrée<br/>Merci d&apos;essayer plus tard",
            "message": "Bonjour,<br/><p>Message reçu de {{nom}} (email {{mail}}), sur le site ({host}}.</p><p>{{message}}</p><br/>Cordialement,<br/><br/>- Admin - ",
        },
        "j_indexs": [
            {
                "language": "fr",
                "uri": "fr/",
                "default": True,
                "nav": "fr_menu",
                "start": "fr_accueil",
                "entry_file": "demo.php",
                "const": {
                    "title": "Démo en francais",
                    "meta": "Lorem ipsum, dolor sit amet consectetur",
                },
            },
            {
                "language": "en",
                "uri": "en/",
                "nav": "en_menu",
                "start": "en_home",
                "entry_file": "demo.php",
                "const": {"title": "English demo", "meta": "Lorem ipsum"},
            },
        ],
        "j_routes": [
            ["GET", "/", "@Index", "www_start"],
            ["GET", "/menu/[**:uri]", "@Index", "www_uri"],
            ["POST", "/api/msg", "@Msg", "message"],
            ["POST|DELETE", "/api/auth", "@Auth", "authen"],
            ["POST|PUT|DELETE", "/api/user", "@User", "user"],
            ["GET|DELETE", "/api/log", "@Log", "log"],
        ],
    },
    {
        "name": "TST",
        "j_option": {"user": "svcstaff", "maintenance": False},
        "j_contact": {
            "mail": "sample@mail.com",
            "msgMax": 2000,
            "sendMail": False,
            "msgOk": "Message envoyé, merci",
            "msgKo": "Une erreur a été rencontrée<br/>Merci d&apos;essayer plus tard",
            "message": "Bonjour,<br/><p>Message reçu de {{nom}} (email {{mail}}), sur le site ({host}}.</p><p>{{message}}</p><br/>Cordialement,<br/><br/>- Admin - ",
        },
        "j_indexs": [
            {
                "language": "fr",
                "default": True,
                "uri": "fr",
                "uri_def": "fr/accueil",
                "title": "Démo en francais",
                "meta": {
                    "description": "Lorem ipsum, dolor sit amet consectetur",
                    "revised": "25/05/2021",
                },
                "index": "demo.html",
            },
            {
                "language": "en",
                "uri": "en",
                "uri_def": "en/home",
                "title": "English demo",
                "index": "demo.html",
            },
        ],
        "j_routes": [
            ["GET", "/", "@Index", "www_start"],
            ["GET", "/menu/[**:uri]", "@Index", "www_uri"],
            ["POST", "/api/msg", "@Msg", "message"],
            ["POST|DELETE", "/api/auth", "@Auth", "authen"],
            ["POST|PUT|DELETE", "/api/user", "@User", "user"],
            ["GET|DELETE", "/api/log", "@Log", "log"],
        ],
    },
    {
        "name": "PRD",
        "j_option": {"maintenance": False, "user": "svcstaff"},
        "j_contact": {
            "mail": "sample@mail.com",
            "msgMax": 2000,
            "purge": 45,
            "sendMail": False,
            "msgOk": "Message envoyé, merci",
            "msgKo": "Une erreur a été rencontrée<br/>Merci d&apos;essayer plus tard",
            "message": "Bonjour,<br/><p>Message reçu de {{nom}} (email {{mail}}), sur le site ({host}}.</p><p>{{message}}</p> <br/>Cordialement,<br/><br/>- Admin - ",
        },
        "j_indexs": [
            {
                "language": "fr",
                "default": True,
                "uri": "fr",
                "uri_def": "fr/accueil",
                "title": "Démo en francais",
                "meta": {
                    "description": "Lorem ipsum, dolor sit amet consectetur",
                    "revised": "25/05/2021",
                },
                "index": "demo.html",
            },
            {
                "language": "en",
                "uri": "en",
                "uri_def": "en/home",
                "title": "English demo",
                "index": "demo.html",
            },
        ],
        "j_routes": [
            ["GET", "/", "@Index", "www_start"],
            ["GET", "/menu/[**:uri]", "@Index", "www_uri"],
            ["POST", "/api/msg", "@Msg", "message"],
            ["POST|DELETE", "/api/auth", "@Auth", "authen"],
            ["POST|PUT|DELETE", "/api/user", "@User", "user"],
            ["GET|DELETE", "/api/log", "@Log", "log"],
        ],
    },
]


datas = [
    {
        "ref": "fr_menu",
        "id_div": "menu",
        "title": "Menu francais",
        "j_content": [
            {
                "name": "Accueil",
                "uri": "@fr/accueil",
                "ref": "fr_accueil",
                "meta": "Accueil du site",
            },
            {"name": "Actualité", "uri": "@fr/news", "ref": "news"},
            {
                "name": "Divers",
                "class": "dropdown",
                "down": True,
                "uri": "#",
                "dropdown": [
                    {"name": "Lorem", "uri": "@fr/more/18", "ref": "lorem"},
                    {"name": "Lien externe...", "uri": "https://www.daldomarte.com"},
                ],
            },
            {"name": "Contact", "uri": "@fr/contact", "ref": "contact"},
            {
                "name": "Langage",
                "class": "dropdown",
                "down": True,
                "uri": "#",
                "dropdown": [{"name": "Anglais", "uri": "@en/home"}],
            },
        ],
    },
    {
        "ref": "en_menu",
        "id_div": "menu",
        "title": "English menu",
        "j_content": [
            {"name": "Home", "uri": "@en/home", "ref": "en_home"},
            {"name": "News", "uri": "@en/news", "ref": "news"},
            {
                "name": "Miscellaneous",
                "class": "dropdown",
                "down": True,
                "uri": "#",
                "dropdown": [
                    {"name": "Lorem", "uri": "@en/more/18", "ref": "lorem"},
                    {"name": "External link...", "uri": "https://www.daldomarte.com"},
                ],
            },
            {"name": "Contact", "uri": "@en/contact", "ref": "contact"},
            {
                "name": "Language",
                "class": "dropdown",
                "down": True,
                "uri": "#",
                "dropdown": [
                    {"name": "French", "uri": "@fr/accueil", "ref": "fr_accueil"},
                    {"name": "English", "uri": "@en/home"},
                ],
            },
        ],
    },
    {
        "ref": "fr_accueil",
        "id_div": "content",
        "rank": 1,
        "title": "Message de bienvenue",
        "j_content": {
            "section": '<div data-reveal="left"> <h1>Bienvenue</h1> <p> Sur le site de demo du framework staff.<br />L\'objectif de framework est de pouvoir construire des sites web vitrines. </p> <img class="img-fluid" src="/images/demo.png" alt="demo image" /> </div>',
        },
    },
    {
        "ref": "fr_accueil",
        "id_div": "content",
        "rank": 2,
        "title": "Prerequis technique",
        "j_content": {"_section": "html/fr_accueil_2.html"},
    },
    {
        "ref": "en_home",
        "id_div": "content",
        "rank": 1,
        "title": "Welcome",
        "j_content": {"_section": "html/en_home_1.html"},
    },
    {
        "ref": "en_home",
        "id_div": "content",
        "rank": 2,
        "title": "Technical prerequisite",
        "j_content": {"_section": "html/en_home_2.html"},
    },
    {
        "ref": "news",
        "id_div": "news",
        "rank": 1,
        "title": "Moscow",
        "j_content": {
            "img_src": "/media/moscou.jpg",
            "img_alt": "Moscow",
            "title": "Moscow",
            "date": "",
            "article": "<p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quos ut, quisquam ducimus commodi dolore dolorum voluptatum officiis, provident exercitationem ratione asperiores quasi perferendis minus? Adipisci necessitatibus reprehenderit distinctio architecto eligendi?</p>",
        },
    },
    {
        "ref": "news",
        "id_div": "news",
        "rank": 3,
        "title": "Londre",
        "j_content": {
            "img_src": "/media/london.jpg",
            "img_alt": "London",
            "title": "London",
            "date": "",
            "article": "<p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quos ut, quisquam ducimus commodi dolore dolorum voluptatum officiis, provident exercitationem ratione asperiores quasi perferendis minus? Adipisci necessitatibus reprehenderit distinctio architecto eligendi?</p><p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quos ut, quisquam ducimus commodi dolore dolorum voluptatum officiis, provident exercitationem ratione asperiores quasi perferendis minus? Adipisci necessitatibus reprehenderit distinctio architecto eligendi?</p><p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quos ut, quisquam ducimus commodi dolore dolorum voluptatum officiis, provident exercitationem ratione asperiores quasi perferendis minus? Adipisci necessitatibus reprehenderit distinctio architecto eligendi?</p>",
        },
    },
    {
        "ref": "news",
        "id_div": "news",
        "rank": 4,
        "title": "Paris",
        "j_content": {
            "img_src": "/media/paris.jpg",
            "img_alt": "Paris tour Eiffel",
            "title": "Paris",
            "date": "08/08/2019",
            "article": "<p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quos ut, quisquam ducimus commodi dolore dolorum voluptatum officiis, provident exercitationem ratione asperiores quasi perferendis minus? Adipisci necessitatibus reprehenderit distinctio architecto eligendi?</p><p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quos ut, quisquam ducimus commodi dolore dolorum voluptatum officiis, provident exercitationem ratione asperiores quasi perferendis minus? Adipisci necessitatibus reprehenderit distinctio architecto eligendi?</p>",
        },
    },
    {
        "ref": "news",
        "id_div": "news",
        "rank": 5,
        "title": "New York",
        "j_content": {
            "img_src": "/media/newyork.jpg",
            "img_alt": "New York",
            "title": "New York",
            "date": "01/05/2021",
            "article": "<p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quos ut, quisquam ducimus commodi dolore dolorum voluptatum officiis, provident exercitationem ratione asperiores quasi perferendis minus? Adipisci necessitatibus reprehenderit distinctio architecto eligendi?</p><p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quos ut, quisquam ducimus commodi dolore dolorum voluptatum officiis, provident exercitationem ratione asperiores quasi perferendis minus? Adipisci necessitatibus reprehenderit distinctio architecto eligendi?</p>",
        },
    },
    {
        "ref": "news",
        "id_div": "news",
        "rank": 6,
        "title": "Tokyo",
        "j_content": {
            "img_src": "/media/tokyo.jpg",
            "img_alt": "Tokyo",
            "title": "Tokyo",
            "article": "<p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quos ut, quisquam ducimus commodi dolore dolorum voluptatum officiis, provident exercitationem ratione asperiores quasi perferendis minus? Adipisci necessitatibus reprehenderit distinctio architecto eligendi?</p>",
        },
    },
    {
        "ref": "lorem",
        "id_div": "content",
        "rank": 1,
        "title": "Lorem...",
        "j_content": {"_section": "html/lorem.html"},
    },
    {
        "ref": "contact",
        "id_div": "content",
        "rank": 1,
        "title": "contact",
        "j_content": {"_section": "html/contact.html"},
    },
]

from flask import Flask
import os

app = Flask(__name__)

@app.route("/")
def hello():
    name = os.getenv("NAME", "Avinash")
    return f"Hello, {name}! This is a Dockerized Flask app."

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=80)
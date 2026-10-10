from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

@app.route('/', methods=['GET'])
def health():
    return jsonify({"status": "healthy", "mode": "mock_active"}), 200

@app.route('/api/chat', methods=['POST'])
def chat():
    return jsonify({
        "choices": [{
            "message": {
                "content": "Hello! Welcome to our site. How can I assist you with your business today?"
            }
        }]
    }), 200

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)

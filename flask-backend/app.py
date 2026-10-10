import os
from flask import Flask, request, jsonify
from flask_cors import CORS
from groq import Groq

app = Flask(__name__)
# Enable CORS for cross-origin requests from WordPress
CORS(app)

@app.route("/", methods=["GET"])
def health():
    return jsonify({
        "status": "healthy",
        "service": "WordPress AI Lead Assistant Backend",
        "model": "llama-3.1-70b-versatile"
    })

@app.route("/api/chat", methods=["POST"])
def chat():
    data = request.get_json() or {}
    user_message = data.get("message", "")

    if not user_message:
        return jsonify({"reply": "Please provide a valid prompt."}), 400

    api_key = os.environ.get("GROQ_API_KEY")
    if not api_key:
        return jsonify({"reply": "Backend Error: GROQ_API_KEY not configured on server."}), 500

    try:
        client = Groq(api_key=api_key)
        completion = client.chat.completions.create(
            messages=[
                {
                    "role": "system",
                    "content": "You are Kvasir AI, an expert B2B lead generation & customer support assistant embedded on a live website. Keep answers helpful, professional, and under 3 concise sentences."
                },
                {
                    "role": "user",
                    "content": user_message
                }
            ],
            model="llama-3.1-70b-versatile"
        )
        reply = completion.choices[0].message.content
        return jsonify({"reply": reply, "status": "success"})
    except Exception as e:
        return jsonify({"reply": f"AI Engine Error: {str(e)}", "status": "error"}), 500

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host="0.0.0.0", port=port)
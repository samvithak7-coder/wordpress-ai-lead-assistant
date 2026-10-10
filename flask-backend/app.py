import os
from flask import Flask, request, jsonify
from flask_cors import CORS
from groq import Groq

app = Flask(__name__)
# Enable CORS for cross-origin requests from WordPress
CORS(app)

def get_active_model(client=None):
    """
    Dynamically fetches active models from Groq's live models API.
    Prefers 'llama-3.3-70b-versatile' or 'llama-3.1-8b-instant',
    otherwise selects the first available active model ID.
    Falls back safely to 'llama-3.3-70b-versatile' if fetching fails.
    """
    fallback_model = "llama-3.3-70b-versatile"
    try:
        if client is None:
            api_key = os.environ.get("GROQ_API_KEY")
            if not api_key:
                return fallback_model
            client = Groq(api_key=api_key)

        models_page = client.models.list()
        model_objs = models_page.data if hasattr(models_page, "data") else []

        active_models = [
            m.id for m in model_objs
            if getattr(m, "active", True) is not False and hasattr(m, "id")
        ]

        if "llama-3.3-70b-versatile" in active_models:
            return "llama-3.3-70b-versatile"
        elif "llama-3.1-8b-instant" in active_models:
            return "llama-3.1-8b-instant"
        elif active_models:
            return active_models[0]
        else:
            return fallback_model
    except Exception:
        return fallback_model

@app.route("/", methods=["GET"])
def health():
    active_model = get_active_model()
    return jsonify({
        "status": "healthy",
        "service": "WordPress AI Lead Assistant Backend",
        "model": active_model
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
        model_to_use = get_active_model(client)
        messages = [
            {
                "role": "system",
                "content": "You are Kvasir AI, an expert B2B lead generation & customer support assistant embedded on a live website. Keep answers helpful, professional, and under 3 concise sentences."
            },
            {
                "role": "user",
                "content": user_message
            }
        ]
        completion = client.chat.completions.create(
            messages=messages,
            model=model_to_use
        )
        reply = completion.choices[0].message.content
        return jsonify({"reply": reply, "status": "success"})
    except Exception as e:
        return jsonify({"reply": f"AI Engine Error: {str(e)}", "status": "error"}), 500

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host="0.0.0.0", port=port)
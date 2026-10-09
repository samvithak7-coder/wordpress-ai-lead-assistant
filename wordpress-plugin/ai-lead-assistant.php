<?php
/**
 * Plugin Name: Smart AI Lead Assistant
 * Plugin URI: https://github.com/samvithak7-coder/wordpress-ai-lead-assistant
 * Description: Interactive AI-powered customer engagement assistant powered by Groq Llama-3.1.
 * Version: 1.0.0
 * Author: Samvitha
 */

if (!defined('ABSPATH')) exit;

class AI_Lead_Assistant {

    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'render_widget'));
    }

    public function enqueue_assets() {
        wp_register_script('ai-assistant-script', false, array(), '1.0', true);
        wp_enqueue_script('ai-assistant-script');

        $backend_url = 'https://wordpress-ai-lead-assistant.onrender.com/api/chat';

        $inline_script = "
            document.addEventListener('DOMContentLoaded', function() {
                const sendBtn = document.getElementById('ai-send-btn');
                const inputField = document.getElementById('ai-user-input');
                const chatBody = document.getElementById('ai-chat-body');

                if (!sendBtn) return;

                async function sendMessage() {
                    const message = inputField.value.trim();
                    if (!message) return;

                    chatBody.innerHTML += '<div class=\"msg user\">' + escapeHtml(message) + '</div>';
                    inputField.value = '';
                    chatBody.scrollTop = chatBody.scrollHeight;

                    const loadingId = 'loading-' + Date.now();
                    chatBody.innerHTML += '<div class=\"msg bot\" id=\"' + loadingId + '\">Thinking...</div>';
                    chatBody.scrollTop = chatBody.scrollHeight;

                    try {
                        const res = await fetch('" . esc_url($backend_url) . "', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ message: message })
                        });
                        const data = await res.json();
                        document.getElementById(loadingId).innerText = data.reply || 'Error receiving response.';
                    } catch (err) {
                        document.getElementById(loadingId).innerText = 'Server error. Please try again.';
                    }
                    chatBody.scrollTop = chatBody.scrollHeight;
                }

                function escapeHtml(text) {
                    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                }

                sendBtn.addEventListener('click', sendMessage);
                inputField.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') sendMessage();
                });
            });
        ";
        wp_add_inline_script('ai-assistant-script', $inline_script);
    }

    public function render_widget() {
        echo '
        <style>
            #ai-chat-widget { position: fixed; bottom: 20px; right: 20px; width: 340px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; z-index: 999999; overflow: hidden; }
            #ai-chat-header { background: #2563eb; color: #ffffff; padding: 12px 16px; font-weight: 600; font-size: 14px; display: flex; align-items: center; justify-content: space-between; }
            #ai-chat-body { height: 260px; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 10px; background: #f8fafc; }
            .msg { padding: 8px 12px; border-radius: 8px; font-size: 13px; line-height: 1.4; max-width: 85%; word-wrap: break-word; }
            .msg.user { background: #2563eb; color: #ffffff; align-self: flex-end; }
            .msg.bot { background: #ffffff; color: #1e293b; border: 1px solid #e2e8f0; align-self: flex-start; }
            #ai-chat-footer { display: flex; border-top: 1px solid #e2e8f0; background: #ffffff; padding: 8px; }
            #ai-user-input { flex: 1; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; font-size: 13px; outline: none; }
            #ai-user-input:focus { border-color: #2563eb; }
            #ai-send-btn { background: #2563eb; color: #ffffff; border: none; border-radius: 6px; padding: 8px 14px; margin-left: 6px; cursor: pointer; font-weight: 500; font-size: 13px; }
            #ai-send-btn:hover { background: #1d4ed8; }
        </style>
        <div id="ai-chat-widget">
            <div id="ai-chat-header">
                <span>🤖 AI Lead Assistant</span>
            </div>
            <div id="ai-chat-body">
                <div class="msg bot">Hello! Welcome to our site. How can I assist you today?</div>
            </div>
            <div id="ai-chat-footer">
                <input type="text" id="ai-user-input" placeholder="Type your message..." />
                <button id="ai-send-btn">Send</button>
            </div>
        </div>';
    }
}

new AI_Lead_Assistant();

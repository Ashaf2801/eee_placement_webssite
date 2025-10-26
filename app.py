"""
EEE Placement AI Chatbot - Flask Backend
Handles API calls from PHP frontend via headers
"""

from flask import Flask, request, jsonify, session
from flask_cors import CORS
from flask_session import Session
import os
import sys
from datetime import timedelta
import logging
from functools import wraps
import mysql.connector
from mysql.connector import Error
import google.generativeai as genai
from dotenv import load_dotenv

# ========================================
# WINDOWS UTF-8 FIX
# ========================================
if sys.platform == "win32":
    os.environ["PYTHONIOENCODING"] = "utf-8"
    try:
        sys.stdout.reconfigure(encoding="utf-8")
        sys.stderr.reconfigure(encoding="utf-8")
    except:
        pass

# Load .env
load_dotenv()

# ========================================
# LOGGING (Windows-safe)
# ========================================
class SafeFormatter(logging.Formatter):
    def format(self, record):
        msg = record.getMessage()
        replacements = {
            'Checkmark': '[OK]', 'Cross Mark': '[ERR]', 'Warning': '[WARN]',
            'Rocket': '[RUN]', 'Robot': '[AI]', 'Book': '[PREP]', 'Speech Balloon': '[MSG]'
        }
        for k, v in replacements.items():
            msg = msg.replace(k, v)
        msg = msg.encode('ascii', 'ignore').decode()
        record.msg = msg
        record.args = ()
        return super().format(record)

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('chatbot.log', encoding='utf-8'),
        logging.StreamHandler(sys.stderr)
    ]
)
logger = logging.getLogger(__name__)
for h in logger.handlers:
    if isinstance(h, logging.StreamHandler):
        h.setFormatter(SafeFormatter())

# ========================================
# FLASK APP
# ========================================
app = Flask(__name__)
CORS(app)  # ← CRITICAL: Allow X-User-ID, X-User-Type

app.config['SECRET_KEY'] = os.environ.get('SECRET_KEY', 'change-me-in-production')
app.config['SESSION_TYPE'] = 'filesystem'
app.config['SESSION_PERMANENT'] = True
app.config['PERMANENT_SESSION_LIFETIME'] = timedelta(hours=2)
app.config['SESSION_COOKIE_SECURE'] = False
app.config['SESSION_COOKIE_HTTPONLY'] = True
app.config['SESSION_COOKIE_SAMESITE'] = 'Lax'

Session(app)

# ========================================
# CONFIG
# ========================================
DB_CONFIG = {
    'host': os.environ.get('MYSQL_HOST', 'localhost'),
    'user': os.environ.get('MYSQL_USER', 'root'),
    'password': os.environ.get('MYSQL_PASSWORD', ''),
    'database': os.environ.get('MYSQL_DATABASE', 'eee_placement'),
    'port': int(os.environ.get('MYSQL_PORT', 3306)),
    'pool_name': 'mypool',
    'pool_size': 5,
    'pool_reset_session': True
}

GEMINI_API_KEY = os.environ.get("GEMINI_API_KEY")
MODEL_NAME = 'gemini-2.5-flash'

# ========================================
# INITIALIZE GEMINI
# ========================================
gemini_model = None
try:
    if GEMINI_API_KEY:
        genai.configure(api_key=GEMINI_API_KEY)
        gemini_model = genai.GenerativeModel(MODEL_NAME)
        logger.info(f"Gemini AI configured: {MODEL_NAME}")
    else:
        logger.warning("GEMINI_API_KEY missing")
except Exception as e:
    logger.error(f"Gemini init failed: {e}")

# ========================================
# DATABASE POOL
# ========================================
connection_pool = None
try:
    connection_pool = mysql.connector.pooling.MySQLConnectionPool(**DB_CONFIG)
    logger.info("Database pool created")
except Error as e:
    logger.error(f"DB pool error: {e}")

# ========================================
# DECORATORS
# ========================================
def login_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        if 'user_id' not in session:
            return redirect(url_for('login'))
        return f(*args, **kwargs)
    return decorated

# ========================================
# DB HELPERS
# ========================================
def get_db_connection():
    try:
        if connection_pool:
            return connection_pool.get_connection()
    except Error as e:
        logger.error(f"DB connect error: {e}")
    return None

def get_database_schema():
    conn = get_db_connection()
    if not conn: return "DB unavailable"
    try:
        cursor = conn.cursor()
        schema = """
        TABLES:
        - company(company_name PK)
        - student(register_no PK, name, phoneno, mail, year_of_graduation)
        - placement(register_no, company_name, company_type, package, round1..6)
        """
        cursor.execute("SELECT DISTINCT company_name FROM placement LIMIT 8")
        companies = [r[0] for r in cursor.fetchall()]
        cursor.execute("SELECT MIN(package), MAX(package), AVG(package) FROM placement WHERE package IS NOT NULL")
        row = cursor.fetchone()
        min_p, max_p, avg_p = row if row else (0, 0, 0)
        cursor.execute("SELECT COUNT(*) FROM placement")
        total = cursor.fetchone()[0]
        sample = f"""
        SAMPLE:
        - Companies: {', '.join(companies) if companies else 'None'}
        - Packages: {min_p}L to {max_p}L (Avg: {avg_p:.2f}L)
        - Total: {total}
        """
        cursor.close()
        return schema + sample
    except Error as e:
        return f"Schema error: {e}"
    finally:
        if conn and conn.is_connected():
            conn.close()

# ========================================
# AI FUNCTIONS
# ========================================
def generate_sql_with_ai(question, user_id="unknown"):
    if not gemini_model: return None
    try:
        db_info = get_database_schema()
        prompt = f"""
        Generate ONLY the SQL query. No explanation.

        DB: {db_info}
        QUESTION: "{question}"
        """
        logger.info(f"AI SQL | {user_id}: {question}")
        response = gemini_model.generate_content(prompt)
        sql = response.text.strip().replace('```sql', '').replace('```', '').strip()
        if sql.endswith(';'): sql = sql[:-1]
        logger.info(f"SQL: {sql}")
        return sql if 'SELECT' in sql.upper() else None
    except Exception as e:
        logger.error(f"SQL gen error: {e}")
        return None

def execute_sql_query(sql):
    conn = get_db_connection()
    if not conn: return {"error": "DB down"}
    cursor = None
    try:
        cursor = conn.cursor()
        cursor.execute(sql)
        if sql.strip().upper().startswith('SELECT'):
            cols = [d[0] for d in cursor.description]
            rows = cursor.fetchall()
            return {"success": True, "columns": cols, "data": rows, "row_count": len(rows)}
        else:
            conn.commit()
            return {"success": True, "message": f"Rows: {cursor.rowcount}"}
    except Error as e:
        return {"error": str(e)}
    finally:
        if cursor: cursor.close()
        if conn and conn.is_connected(): conn.close()

def analyze_with_ai(question, results, user_id="unknown"):
    if not gemini_model: return "AI down"
    try:
        data = ""
        if results.get("success") and results.get("row_count", 0) > 0:
            cols = results["columns"]
            rows = results["data"][:10]
            data = "RESULTS:\n" + " | ".join(cols) + "\n" + "-"*50 + "\n"
            for r in rows:
                data += " | ".join(str(x) if x is not None else "NULL" for x in r) + "\n"
        prompt = f"QUESTION: {question}\n{data or 'No data.'}\nAnswer clearly for EEE students."
        logger.info(f"AI Analysis | {user_id}")
        return gemini_model.generate_content(prompt).text
    except Exception as e:
        return "Analysis failed."

def direct_ai_answer(question, user_id="unknown"):
    if not gemini_model: return "AI unavailable"
    try:
        db_info = get_database_schema()
        prompt = f"EEE Advisor\nDB: {db_info}\nQUESTION: {question}\nGive practical advice."
        return gemini_model.generate_content(prompt).text
    except Exception as e:
        return "AI error"

# ========================================
# ROUTES - HTML (Require Login)
# ========================================
@app.route('/')
def index():
    return redirect(url_for('login') if 'user_id' not in session else url_for('chatbot'))

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')
        if username and password:  # Replace with real auth
            session['user_id'] = username
            session['username'] = username
            session['user_type'] = 'student'
            return redirect(url_for('chatbot'))
    return '''
    <form method="post">
        <input name="username" placeholder="Username" required><br><br>
        <input name="password" type="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>
    '''

@app.route('/logout')
def logout():
    session.clear()
    return redirect(url_for('login'))

@app.route('/chatbot')
@login_required
def chatbot():
    return '''
    <h2>Hello, {username}!</h2>
    <p>Use the PHP frontend at <a href="/chatbot.php">chatbot.php</a></p>
    '''.format(username=session.get('username', 'User'))

# ========================================
# API ROUTES - NO @login_required (Use Headers)
# ========================================
@app.route('/api/chat', methods=['POST'])
def api_chat():
    try:
        data = request.get_json()
        message = data.get('message', '').strip()
        if not message:
            return jsonify({'success': False, 'error': 'Empty message'})

        user_id = request.headers.get('X-User-ID', 'unknown')
        user_type = request.headers.get('X-User-Type', 'unknown')
        logger.info(f"CHAT | {user_id} ({user_type}): {message}")

        sql = generate_sql_with_ai(message, user_id)
        if not sql:
            return jsonify({'success': True, 'response': direct_ai_answer(message, user_id), 'type': 'direct'})

        result = execute_sql_query(sql)
        if 'error' in result:
            return jsonify({'success': True, 'response': direct_ai_answer(message, user_id), 'type': 'fallback'})

        analysis = analyze_with_ai(message, result, user_id)
        return jsonify({'success': True, 'response': analysis, 'type': 'ai_analysis'})

    except Exception as e:
        logger.error(f"Chat error: {e}")
        return jsonify({'success': False, 'error': 'Server error'})

@app.route('/api/companies', methods=['GET'])
def api_companies():
    user_id = request.headers.get('X-User-ID', 'unknown')
    conn = get_db_connection()
    if not conn:
        return jsonify({'success': False, 'error': 'DB down'})
    try:
        cursor = conn.cursor()
        cursor.execute("SELECT DISTINCT company_name FROM placement ORDER BY company_name")
        companies = [row[0] for row in cursor.fetchall()]
        return jsonify({'success': True, 'companies': companies})
    except Error as e:
        return jsonify({'success': False, 'error': str(e)})
    finally:
        if conn and conn.is_connected():
            conn.close()

@app.route('/api/interview-prep', methods=['POST'])
def api_interview_prep():
    try:
        data = request.get_json()
        company = data.get('company', '').strip()
        if not company:
            return jsonify({'success': False, 'error': 'Company required'})

        user_id = request.headers.get('X-User-ID', 'unknown')
        logger.info(f"PREP | {user_id}: {company}")

        conn = get_db_connection()
        company_data = ""
        if conn:
            cursor = conn.cursor()
            cursor.execute("SELECT round1, round2, round3, company_type, package FROM placement WHERE company_name = %s LIMIT 3", (company,))
            rows = cursor.fetchall()
            if rows:
                company_data = "PAST DATA:\n"
                for i, r in enumerate(rows, 1):
                    pkg = f"{r[4]}L" if r[4] else "N/A"
                    company_data += f"- {i}: Rounds=({r[0]}, {r[1]}, {r[2]}), Package={pkg}\n"
            cursor.close()
            conn.close()

        prompt = f"Interview prep for {company} (EEE)\n{company_data or 'No data'}\nInclude rounds, skills, tips."
        response = gemini_model.generate_content(prompt)
        return jsonify({'success': True, 'response': response.text})
    except Exception as e:
        logger.error(f"Prep error: {e}")
        return jsonify({'success': False, 'error': str(e)})

@app.route('/api/test', methods=['GET'])
def api_test():
    user_id = request.headers.get('X-User-ID', 'unknown')
    db = "Database Error"
    ai = "AI Error"

    conn = get_db_connection()
    if conn:
        db = "Database Connected"
        conn.close()

    if gemini_model:
        try:
            resp = gemini_model.generate_content("Say OK")
            if resp and hasattr(resp, 'text'):
                ai = "AI Working"
        except:
            pass

    return jsonify({'success': True, 'db_status': db, 'ai_status': ai})

# ========================================
# ERROR HANDLERS
# ========================================
@app.errorhandler(404)
def not_found(e):
    return jsonify({'error': 'Not found'}), 404

@app.errorhandler(500)
def server_error(e):
    logger.error(f"Server error: {e}")
    return jsonify({'error': 'Server error'}), 500

# ========================================
# MAIN
# ========================================
if __name__ == '__main__':
    if not GEMINI_API_KEY:
        logger.warning("GEMINI_API_KEY missing!")
    if not connection_pool:
        logger.warning("DB pool failed!")

    logger.info("Starting Flask AI Server on 0.0.0.0:5001")
    app.run(host='0.0.0.0', port=5001, debug=False, threaded=True)
import sys
import os

# --- ADD THIS CODE BLOCK FOR WINDOWS FIX ---
if sys.platform == 'win32':
    # Force stdout to use UTF-8 encoding
    sys.stdout.reconfigure(encoding='utf-8')

import mysql.connector
from mysql.connector import Error
import google.generativeai as genai
# ... other imports
from dotenv import load_dotenv # ⬅️ ADD THIS IMPORT
import logging

# Load environment variables from .env file
load_dotenv() # ⬅️ ADD THIS LINE AT THE VERY TOP

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class AIPoweredAgent:
    def __init__(self):
        # --- 1. Database Configuration (Read ONLY from ENV) ---
        self.db_config = {
            'host': os.environ.get('MYSQL_HOST'),
            'user': os.environ.get('MYSQL_USER'), 
            'password': os.environ.get('MYSQL_PASSWORD'),
            'database': os.environ.get('MYSQL_DATABASE'),
            'port': int(os.environ.get('MYSQL_PORT', 3306))
        }

        # Fallback for missing DB variables (optional)
        if not all([self.db_config['host'], self.db_config['user'], self.db_config['database']]):
            logger.error("❌ Critical: Database environment variables are missing. Using XAMPP defaults.")
            self.db_config['host'] = 'localhost' 
            self.db_config['user'] = 'root'
            self.db_config['password'] = '' 
            self.db_config['database'] = 'eee_placement'

        # --- 2. Gemini AI Configuration ---
        self.gemini_api_key = os.environ.get("GEMINI_API_KEY") 
        # ... rest of __init__

        if not self.gemini_api_key:
            logger.error("❌ GEMINI_API_KEY environment variable not set.")
            # If you absolutely must, you can use a fallback, but environment variables are best:
            # self.gemini_api_key = "AIzaSyDqjHK3PUeV8hlYkCr-N4xAQwYw7gmP20Q" # USE AT YOUR OWN RISK/TEMPORARILY

        self.model_name = 'gemini-2.5-flash' # Using a powerful and fast free-tier model
        self.configure_gemini()
    
    def configure_gemini(self):
        """Configure Google Gemini AI with correct models"""
        try:
            genai.configure(api_key=self.gemini_api_key)
            
            # Simple check to see if the model is accessible
            self.model = genai.GenerativeModel(self.model_name)
            logger.info(f"✅ Gemini AI configured with model: {self.model_name}")
            
        except Exception as e:
            logger.error(f"❌ Error configuring Gemini. Check API Key/Network: {e}")
            raise

    # (get_connection, get_database_info methods remain largely the same)
    
    def get_connection(self):
        """Get database connection"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            logger.info("✅ Database connected successfully")
            return connection
        except Error as e:
            logger.error(f"❌ Database connection error: {e}")
            return None
    
    def get_database_info(self):
        """Get database schema and sample data for AI context"""
        conn = self.get_connection()
        if not conn:
            return "Database connection failed"
        
        try:
            cursor = conn.cursor()
            
            # Get schema info (unchanged)
            schema = """
            DATABASE: eee_placement
            
            TABLES:
            1. company (company_name VARCHAR(255) PRIMARY KEY)
            
            2. student (
                register_no VARCHAR(20) PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                phoneno VARCHAR(20) UNIQUE,
                mail VARCHAR(150) UNIQUE, 
                year_of_graduation INT NOT NULL
            )
            
            3. placement (
                register_no VARCHAR(20),
                company_name VARCHAR(255),
                company_type VARCHAR(20),
                package DECIMAL(10,2),
                round1 TEXT, round2 TEXT, round3 TEXT,
                round4 TEXT, round5 TEXT, round6 TEXT,
                PRIMARY KEY (register_no, company_name),
                FOREIGN KEY (company_name) REFERENCES company(company_name),
                FOREIGN KEY (register_no) REFERENCES student(register_no)
            )
            """
            
            # Get sample data (unchanged)
            cursor.execute("SELECT DISTINCT company_name FROM placement LIMIT 8")
            companies = [row[0] for row in cursor.fetchall()]
            
            cursor.execute("SELECT MIN(package), MAX(package), AVG(package) FROM placement WHERE package IS NOT NULL")
            pkg_result = cursor.fetchone()
            min_pkg, max_pkg, avg_pkg = pkg_result if pkg_result else (0, 0, 0)
            
            cursor.execute("SELECT DISTINCT round1 FROM placement WHERE round1 IS NOT NULL LIMIT 5")
            rounds = [row[0] for row in cursor.fetchall()]
            
            cursor.execute("SELECT COUNT(*) FROM placement")
            total_placements = cursor.fetchone()[0]
            
            sample_data = f"""
            SAMPLE DATA:
            - Companies: {', '.join(companies) if companies else 'No companies found'}
            - Packages: {min_pkg}L to {max_pkg}L (Average: {avg_pkg:.2f}L)
            - Total Placements: {total_placements}
            - Interview Rounds: {', '.join(rounds) if rounds else 'No round data'}
            """
            
            cursor.close()
            return schema + sample_data
            
        except Error as e:
            return f"Schema error: {str(e)}"
        finally:
            conn.close()

    def generate_sql_with_ai(self, question):
        """Use AI to generate SQL query"""
        try:
            db_info = self.get_database_info()
            
            prompt = f"""
            You are an expert MySQL query generator. Based on this database schema, generate a MySQL query to answer the question.

            DATABASE INFORMATION:
            {db_info}

            QUESTION: "{question}"

            INSTRUCTIONS:
            1. Generate ONLY the SQL query without any markdown fences (like ```sql) or explanations.
            2. Use proper JOINs between tables when needed.
            3. Handle NULL values appropriately.
            4. Make the query efficient and correct.
            5. Return ONLY the SQL query, nothing else.

            SQL QUERY:
            """
            
            logger.info("🤖 Generating SQL with AI...")
            response = self.model.generate_content(prompt)
            if not response or not hasattr(response, 'text'):
                logger.error("❌ Gemini API did not return text")
                return None
            
            sql_query = response.text.strip()
            
            # --- Robust Query Cleaning ---
            sql_query = sql_query.replace('```sql', '').replace('```', '')
            sql_query = sql_query.replace('```mysql', '').strip() 
            if sql_query.endswith(';'): 
                sql_query = sql_query[:-1] # Remove trailing semicolon

            # Validate it's a SQL query
            if not any(keyword in sql_query.upper() for keyword in ['SELECT', 'FROM', 'UPDATE', 'DELETE']):
                logger.warning(f"AI didn't generate valid SQL: {sql_query[:50]}...")
                return None
                
            logger.info(f"✅ AI Generated SQL: {sql_query}")
            return sql_query
            
        except Exception as e:
            if "quota" in str(e).lower() or "429" in str(e):
                logger.error("❌ Gemini API quota exceeded. Please check your plan and billing.")
                return "❌ Gemini API quota exceeded. Please check your plan and billing."
            logger.error(f"❌ AI SQL generation error: {e}")
            return None
    
    def execute_sql_query(self, sql_query):
        """Execute SQL query and return results"""
        conn = self.get_connection()
        if not conn:
            return {"error": "Database connection failed"}
        
        try:
            cursor = conn.cursor()
            cursor.execute(sql_query)
            
            if sql_query.strip().upper().startswith('SELECT'):
                results = cursor.fetchall()
                columns = [desc[0] for desc in cursor.description]
                
                return {
                    "success": True,
                    "columns": columns,
                    "data": results,
                    "row_count": len(results)
                }
            else:
                conn.commit()
                return {
                    "success": True,
                    "message": f"Query executed successfully. Rows affected: {cursor.rowcount}"
                }
                
        except Error as e:
            return {"error": f"SQL Error: {str(e)}"}
        finally:
            if 'cursor' in locals():
                cursor.close()
            conn.close()
    
    def analyze_with_ai(self, question, sql_results):
        """Use AI to analyze and explain results"""
        try:
            # Format data for AI
            data_str = ""
            if sql_results.get("success") and "columns" in sql_results:
                data_str = "QUERY RESULTS:\n"
                
                if sql_results["row_count"] > 0:
                    data_str += " | ".join(sql_results["columns"]) + "\n"
                    data_str += "-" * 50 + "\n"
                    
                    for row in sql_results["data"][:15]:  # First 15 rows
                        data_str += " | ".join(str(item) if item is not None else "NULL" for item in row) + "\n"
                    
                    if len(sql_results["data"]) > 15:
                        data_str += f"\n... and {len(sql_results['data']) - 15} more rows (Total: {sql_results['row_count']})"
                else:
                    data_str += "Query ran successfully but returned 0 rows (No matching data found)."


            prompt = f"""
            QUESTION: {question}
            
            {data_str}
            
            Please provide a comprehensive, helpful analysis:
            1. Directly answer the original question based on the data provided.
            2. If no data was returned, explain that clearly and suggest why.
            3. Provide context and recommendations relevant to EEE placement.
            4. Keep it conversational and easy to understand for a student.
            
            Focus on being helpful and informative.
            """
            
            logger.info("🤖 Analyzing results with AI...")
            response = self.model.generate_content(prompt)
            return response.text
            
        except Exception as e:
            logger.error(f"❌ AI analysis error: {e}")
            return f"Data retrieved but AI analysis failed. Error: {str(e)}"
    
    def handle_ai_query(self, question):
        """Main method to handle AI-powered queries"""
        try:
            logger.info(f"🧠 Processing: {question}")
            
            # Step 1: Generate SQL with AI
            sql_query = self.generate_sql_with_ai(question)
            if not sql_query or sql_query.startswith("❌ Gemini API quota"):
                # If SQL generation fails or hits quota, use direct AI fallback
                return self.direct_ai_answer(question)
            
            # Step 2: Execute query
            query_results = self.execute_sql_query(sql_query)
            
            # Step 3: Analyze with AI
            if "error" in query_results:
                logger.warning(f"SQL execution failed: {query_results['error']}, falling back to direct AI.")
                return self.direct_ai_answer(question)
            
            # Step 4: Get AI analysis (handles 0 rows internally)
            analysis = self.analyze_with_ai(question, query_results)
            return analysis
            
        except Exception as e:
            logger.error(f"❌ AI query handling error: {e}")
            return f"🤖 I encountered a system error: {str(e)}\n\nPlease try a different question."
    
    def direct_ai_answer(self, question):
        """Use AI to answer directly when database/SQL fails or for general queries"""
        try:
            db_info = self.get_database_info()
            
            prompt = f"""
            You are a helpful EEE placement advisor. Based on this database schema and sample information, answer the user's question helpfully.

            DATABASE INFORMATION (Use this to inform trends and data types, even if you can't query it):
            {db_info}

            QUESTION: {question}

            Please provide a comprehensive, practical answer about:
            - Placement statistics and trends (based on DB info)
            - Company information and packages  
            - Interview preparation advice
            - Study guidance for EEE students
            
            If the question implies fetching specific data that could not be retrieved, clearly state that the database query failed but still provide general, helpful advice.
            """
            
            logger.info("🤖 Generating direct AI answer (Fallback)...")
            response = self.model.generate_content(prompt)
            return response.text
            
        except Exception as e:
            return f"❌ AI service error during fallback: {str(e)}"

    # (get_ai_interview_prep method remains largely the same)

    def get_ai_interview_prep(self, company_name):
        """Get AI-powered interview preparation"""
        try:
            # Get actual data if available
            conn = self.get_connection()
            company_data = ""
            
            if conn:
                cursor = conn.cursor()
                # Fetch placement history for context
                cursor.execute("""
                    SELECT round1, round2, round3, company_type, package 
                    FROM placement 
                    WHERE company_name = %s 
                    LIMIT 3
                """, (company_name,))
                results = cursor.fetchall()
                cursor.close()
                conn.close()
                
                if results:
                    company_data = "ACTUAL PLACEMENT DATA SAMPLES:\n"
                    for i, row in enumerate(results, 1):
                        # Format the data nicely
                        package = f"{row[4]}L" if row[4] else "N/A"
                        company_data += f"- Record {i}: Rounds=({row[0]}, {row[1]}, {row[2]}), Type={row[3]}, Package={package}\n"
            
            db_info = self.get_database_info()
            
            prompt = f"""
            Create a comprehensive interview preparation guide for {company_name} specifically for Electrical and Electronics Engineering (EEE) students.

            DATABASE CONTEXT:
            {db_info}

            {company_data if company_data else "No specific placement data available for this company in the database."}

            Please provide:

            🎯 COMPANY-SPECIFIC PREPARATION:
            - Typical interview process for {company_name} (based on available data/general knowledge)
            - Technical focus areas for EEE students (e.g., Power, Control, VLSI)
            - Recent trends and expectations

            📚 TECHNICAL SKILLS REQUIRED:
            - Core EEE subjects to master.
            - Programming languages and tools common in EEE/CS roles.

            🚀 SUCCESS TIPS:
            - How to stand out as an EEE candidate.
            - Common pitfalls to avoid.

            Make it extremely practical, specific to an EEE background, and actionable.
            """
            
            logger.info(f"🤖 Generating interview prep for {company_name}")
            response = self.model.generate_content(prompt)
            return response.text
            
        except Exception as e:
            return f"❌ AI preparation error: {str(e)}"

def main():
    if len(sys.argv) < 3:
        print("❌ Usage: python gemini_sql_agent.py <query_type> <input>")
        print("Example: python gemini_sql_agent.py query 'Which companies offer the best packages?'")
        print("Example: python gemini_sql_agent.py interview 'Google'")
        sys.exit(1)
    
    query_type = sys.argv[1]
    input_data = sys.argv[2]
    
    # Check for API Key before proceeding
    if not os.environ.get("GEMINI_API_KEY"):
         print("\n=======================================================")
         print("🚨 CRITICAL ERROR: GEMINI_API_KEY is not set.")
         print("Please run this command in your terminal first:")
         print("export GEMINI_API_KEY='YOUR_ACTUAL_API_KEY_HERE'")
         print("=======================================================\n")
         sys.exit(1)

    try:
        agent = AIPoweredAgent()
        
        if query_type == 'query':
            result = agent.handle_ai_query(input_data)
            print(result)
        elif query_type == 'interview':
            result = agent.get_ai_interview_prep(input_data)
            print(result)
        elif query_type == 'test':
            print("🧪 Testing AI System...")
            print("✅ AI Agent initialized successfully!")
            test_result = agent.handle_ai_query("What is the average package for EEE students and which company offers the highest?")
            print("\n📊 Test Result:")
            print(test_result)
        else:
            print("❌ Invalid query type. Use 'query', 'interview', or 'test'")
            
    except Exception as e:
        print(f"❌ System initialization or critical error: {str(e)}")
        print("\n🔧 Troubleshooting Checklist:")
        print("1. ✅ Check your Gemini API key (it must be set as an environment variable).")
        print("2. ✅ Ensure your MySQL database is running and accessible (host/port/credentials).")
        print("3. ✅ Verify the 'eee_placement' database and tables exist with data.")
        

if __name__ == "__main__":
    main()
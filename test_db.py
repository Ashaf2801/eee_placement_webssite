print("🔧 Testing Python Setup...")

try:
    from flask import Flask
    print("✅ Flask: OK")
except:
    print("❌ Flask: FAILED")

try:
    import mysql.connector
    print("✅ MySQL Connector: OK")
except:
    print("❌ MySQL Connector: FAILED")

try:
    import google.generativeai as genai
    print("✅ Google AI: OK")
except:
    print("❌ Google AI: FAILED")

print("\n🎯 Testing Database Connection...")
try:
    conn = mysql.connector.connect(
        host='localhost',
        user='root', 
        password='',
        database='eee_placement'
    )
    print("✅ Database: CONNECTED")
    conn.close()
except Exception as e:
    print(f"❌ Database: FAILED - {e}")

print("\n✨ Setup test completed!")
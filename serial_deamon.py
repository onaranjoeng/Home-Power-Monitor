# LATEST UPDATE 3/23/24
# READS SENSOR DATA AND CREATES/STORES IN DATABASE
#!/usr/bin/python3
import serial
import sqlite3
import requests
import time

# Database setup
db_connection = sqlite3.connect('power_data.db')
cursor = db_connection.cursor()
cursor.execute('''
    CREATE TABLE IF NOT EXISTS power_data (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        CT1 REAL,
        CT2 REAL,
        CT3 REAL,
        real_power_CT1 REAL,
        real_power_CT2 REAL,
        real_power_CT3 REAL,
        AppaPower1 REAL,
        AppaPower2 REAL,
        AppaPower3 REAL,
        Irms1 REAL,
        Irms2 REAL,
        Irms3 REAL,
        Vrms1 REAL,
        Vrms2 REAL,
        Vrms3 REAL,
        PowerFactor1 REAL,
        PowerFactor2 REAL,
        PowerFactor3 REAL,
        datetime DATETIME DEFAULT CURRENT_TIMESTAMP
    )
''')
db_connection.commit()

# Serial port setup
ser = serial.Serial('/dev/ttyAMA0', 38400)

isOpen = ser.isOpen(); 

def send_to_php(message):
    # Send a message to the PHP web application
    requests.post('http://localhost/index.php', data={'message': message})

try:
    while True:
        try:
            line = ser.readline().decode(errors='replace').strip()
            Z = line.split(' ')

            if len(Z) > 15:
                print("----------")
                print("          \tCT1\tCT2\tCT3")
                real_power_CT1 = Z[1]
                real_power_CT2 = Z[6]
                real_power_CT3 = Z[11]
                print(f"RealPower:\t{real_power_CT1}\t{real_power_CT2}\t{real_power_CT3}")

                # Save to SQLite database
                cursor.execute('''
                    INSERT INTO power_data (real_power_CT1, real_power_CT2, real_power_CT3)
                    VALUES (?, ?, ?)
                ''', (real_power_CT1, real_power_CT2, real_power_CT3))
                db_connection.commit()

                # Send data to PHP web application
                # send_to_php(f"New data: CT1={real_power_CT1}, CT2={real_power_CT2}, CT3={real_power_CT3}")
        except serial.SerialException as e:
            print(f"SerialException: {e}")
            print("Attempting to reconnect...")
            ser.close()
            time.sleep(5)
            try:
                ser = serial.Serial('/dev/ttyAMA0', 38400)
                print("Reconnected to serial port.")
            except serial.SerialException as e:
                print(f"Failed to reconnect: {e}")
except KeyboardInterrupt:
    print("Exiting...")
    ser.close()
finally:
    db_connection.close()

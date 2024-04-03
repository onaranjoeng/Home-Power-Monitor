# LATEST UPDATE 4/2/24
# READS SENSOR DATA AND CREATES/STORES IN DATABASE
#!/usr/bin/python3
import serial
import sqlite3
import requests
import time
import subprocess

# Database setup
db_connection = sqlite3.connect('/var/www/html/power_data.db')
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
cursor.execute('''
    CREATE TABLE IF NOT EXISTS power_data_settings (
        home_name VARCHAR(30),
        ct1_label VARCHAR(10),
        ct2_label VARCHAR(10),
        KiloWatt_US REAL,       
        daemon_status VARCHAR(10) DEFAULT 'stopped'
    )
''')

cursor.execute('SELECT COUNT(*) FROM power_data_settings')
if cursor.fetchone()[0] == 0:
    # If the table is empty, insert a new row
    cursor.execute('''
    INSERT INTO power_data_settings (home_name, ct1_label, ct2_label, KiloWatt_US, daemon_status)
    VALUES (?, ?, ?, ?, ?)
    ''', ('My Home', 'CT1', 'CT2', 0.1137, 'stopped'))
    

db_connection.commit()

# kill switch for /dev/ttyAMA0
try:
    subprocess.run(['sudo', 'fuser', '-k', '/dev/ttyAMA0'], check=True)
except subprocess.CalledProcessError as e:
    print(f"Failed to kill process: {e}")

# Serial port setup
ser = serial.Serial('/dev/ttyAMA0', 38400)

isOpen = ser.isOpen(); 

#def send_to_php(message):
#    # Send a message to the PHP web application
#    requests.post('http://localhost/index.php', data={'message': message})

log_file_path = '/var/log/log_serial_daemon.txt'

# Function to write errors to the log file
def log_error(error_message):
    with open(log_file_path, 'a') as log_file:
        log_file.write(error_message + '\n')



try:
    while True:
        try:
            cursor.execute('SELECT daemon_status FROM power_data_settings')
            result = cursor.fetchone()

            # Check if the result is None
            if result is not None:
                 
                # Extract the daemon_status value from the result
                daemon_status = result[0]

                # Use an if statement to check the value of daemon_status
                if daemon_status == 'running':

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
                        time.sleep(1)
                        # Send data to PHP web application
                        # send_to_php(f"New data: CT1={real_power_CT1}, CT2={real_power_CT2}, CT3={real_power_CT3}")
        except serial.SerialException as e:
            log_error(f"SerialException: {e}")
            log_error("Attempting to reconnect...")
            ser.close()
            time.sleep(5)
            try:
                try:
                    subprocess.run(['sudo', 'fuser', '-k', '/dev/ttyAMA0'], check=True)
                except subprocess.CalledProcessError as e:
                    print(f"Failed to kill process: {e}")

                ser = serial.Serial('/dev/ttyAMA0', 38400)
                log_error("Reconnected to serial port.")
            except serial.SerialException as e:
                log_error(f"Failed to reconnect: {e}")
        except sqlite3.Error as e:
            log_error(f"Database error: {e}")
except KeyboardInterrupt:
    print("Exiting...")
    ser.close()
finally:
    db_connection.close()

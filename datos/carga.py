import psycopg2
import psycopg2.extras
import csv

conn = psycopg2.connect(host="localhost", 
    database="cc3201", 
    user="cc3201", 
    password="hoste.aciano.rodas.arre", 
    port="5432")


cur = conn.cursor()


def findOrInsert(table, name):
    cur.execute("select id from "+table+" where name=%s limit 1", [name])
    r = cur.fetchone()
    if(r):
        return r[0]
    else:
        cur.execute("insert into "+table+" (name) values (%s) returning id", [name])
        return cur.fetchone()[0]


def intOrNone(v):
    try:
        return int(v)
    except:
        pass

def floatOrNone(v):
    try:
        return float(v)
    except:
        pass


with open('US_Accidents_Dec21_updated.csv') as csvfile:
    reader = csv.reader(csvfile, delimiter=',', quotechar='"')
    i = 0
    for row in reader:
        i+=1
        if i==1:
            continue
        print(i)

        # Accidentes
        severity = intOrNone(row[1])
        start_time = row[2].strip()
        start_lat = floatOrNone(row[4])
        start_lng = floatOrNone(row[5])
        distance = floatOrNone(row[8])
        description = row[9].strip()

        # Ubicacion (occurs_at)
        street = row[11].strip()
        city = row[13].strip()
        county = row[14].strip()

        # Clima
        if row[29].strip(): # Previene insercion de filas vacias
            temperature = floatOrNone(row[21])
            humidity = intOrNone(row[23])
            visibility = floatOrNone(row[25])
            wind_speed = floatOrNone(row[27])
            weather_condition = row[29].strip()

        # Select or insert Elementos cercanos 
        nearbyelements = row[35].strip(',')
        nearby_elements = [m.strip() for m in nearbyelements.split(',')] 
        nearby_elements_id = []
        for e in nearby_elements:
            if e: # previene insercion de fila vacia
                nearby_elements_id.append(findOrInsert("NearbyElement", e))

        # Select or insert accident
        cur.execute("select id from accident where description=%s \
            and severity=%s and start_time=%s and start_lat=%s and start_lng=%s and distance=%s", 
            [description, severity, start_time, start_lat, start_lng, distance])
        r = cur.fetchone()
        acc_id = None
        if(r):
            acc_id = r[0]
        else:
            cur.execute("insert into accident \
                (description, severity, start_time, start_lat, start_lng, distance) \
                    values (%s,%s,%s,%s,%s,%s) returning id", 
                [description, severity, start_time, start_lat, start_lng, distance])
            acc_id = cur.fetchone()[0]

        # Select or insert weather
        cur.execute("select id from Weather where temperature=%s \
            and humidity=%s and visibility=%s and wind_speed=%s and weather_condition=%s", 
            [temperature, humidity, visibility, wind_speed, weather_condition])
        r = cur.fetchone()
        weather_id = None
        if(r):
            weather_id = r[0]
        else:
            cur.execute("insert into Weather \
                (temperature, humidity, visibility, wind_speed, weather_condition) \
                    values (%s,%s,%s,%s,%s) returning id", 
                [temperature, humidity, visibility, wind_speed, weather_condition])
            weather_id = cur.fetchone()[0]
        
        if(acc_id):
            # Select or insert relation accident_location
            cur.execute("select * from accident_location where (accident_id, L_City, L_County, \
                L_Street) = (%s, %s, %s, %s) limit 1", [acc_id, city, county, street])
            if(not cur.fetchone()):
                cur.execute("insert into accident_location (accident_id, L_City, L_County, \
                    L_Street) values (%s, %s, %s, %s)", [acc_id, city, county, street])
            
            # Select or insert relation accident_nearbyelement
            for nearby_elements_id in nearby_elements_id:
                cur.execute("select * from accident_nearbyelement where (accident_id, nearby_element_id) = (%s, %s) \
                limit 1", [acc_id, nearby_elements_id])
                if(not cur.fetchone()):
                    cur.execute("insert into accident_nearbyelement (accident_id, nearby_element_id) values (%s, %s)",
                    [acc_id, nearby_elements_id])

            # Select or insert relation accident_weather
            cur.execute("select * from accident_weather where (accident_id, weather_id) = (%s, %s) limit 1", 
                [acc_id, weather_id])
            if(not cur.fetchone()):
                cur.execute("insert into accident_weather (accident_id, weather_id) values (%s, %s)", 
                    [acc_id, weather_id])
 
    conn.commit()
        

conn.close()

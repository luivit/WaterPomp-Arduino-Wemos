#if defined(ESP8266)
#include <pgmspace.h>
#else
#include <avr/pgmspace.h>
#endif

#include <NTPClient.h>
#include <WiFiUdp.h>
#include <Pushover.h>
#include <Wire.h>
#include <RtcDS3231.h>
#include <EEPROM.h>
#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include <ESP8266WebServer.h>
#include <Crypto.h>
#include <MySQL_Connection.h>
#include <MySQL_Cursor.h>

#define KEY_LENGTH 6 //number of char to your secret key
byte key[KEY_LENGTH] = {115, 101, 99, 114, 101, 116}; //Int that corresponds to the character, default SECRET (S=115,E=101, etc)

RtcDS3231<TwoWire> Rtc(Wire);
ESP8266WebServer server(80);
IPAddress server_addr(192, 168, 10, 5); // IP of the MySQL *server* here
Pushover pushover = Pushover("myAppToken","myUserToken"); // pushover api, Pushover("myAppToken","myUserToken")
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "0.it.pool.ntp.org");
WiFiClient client;
MySQL_Connection conn((Client *)&client);

const char* ssid = "WIFI-SSID"; // wifi ssid
const char* password = "wifipsw"; //wifi psw
char user[] = "username";              // MySQL user login username
char pasw[] = "password";        // MySQL user login password
const int opened = 14; //pin open signal
const int closed = 12; //pin close signal
const int inputA = 13; //pin output A-I1 (H-bridge)
const int inputB = 15; //pin output A-I2 (H-bridge)
const int buttonClose = 16; //pin button to close
const int pinLedOpen = 2; //pin led opened
#define RtcSquareWavePin 0 //pin SQW timer

byte active = 0;
byte active_addr = 4;
byte frequenza = 0; //frequenza
byte frequenza_addr = 0;
byte ora = 0; //hour
byte ora_addr = 1;
byte minuti = 0; //minute
byte minuti_addr = 2;
byte duratab = 0; //Opened time
byte duratab_addr = 3;
byte durata = 0; //Opened time manual
byte daysCount = 0;
byte daysCount_addr = 5;
byte interruptCountMinute = 0;
byte ntpOffset = 1;
byte ntpOffset_addr = 6;
bool alarm2Count = false;
byte interruptCountMinuteManual = 0;
bool alarm2CountManual = false;
bool checkFreq = false;
bool checkDurata = false;
bool checkDurataManual = false;
char INSERT_OPEN[] = "INSERT INTO valvola.log (id_conf,msg) VALUES (5,'OPEN')";
char INSERT_CLOSE[] = "INSERT INTO valvola.log (id_conf,msg) VALUES (5,'CLOSE')";


/*Get Hash for security*/
String gen_hash(char* msg) {
  SHA256HMAC hmac(key, KEY_LENGTH);
  hmac.doUpdate(msg);
  byte authCode[SHA256HMAC_SIZE];
  hmac.doFinal(authCode);
  String hash = "";
  for (byte i = 0; i < SHA256HMAC_SIZE; i++)
  {
    if (authCode[i] < 0x10) {
      hash = hash + "0";
    }
    hash = hash + String(authCode[i], HEX);
  }
  return hash;
}
/*Get State of valve (open/close)*/
String stato() {
  digitalWrite(inputA, LOW);
  digitalWrite(inputB, LOW);
  if ((digitalRead(opened) == HIGH) && (digitalRead(closed) == LOW)) {
    return "aperto";
  } else if ((digitalRead(opened) == LOW) && (digitalRead(closed) == HIGH)) {
    return "chiuso";
  }
}
/*Close valve*/
boolean chiudi() {
  if (((digitalRead(opened) == HIGH) && (digitalRead(closed) == LOW)) || ((digitalRead(opened) == LOW) && (digitalRead(closed) == LOW))) {
    Serial.print("Sto chiudendo");
    digitalWrite(inputA, HIGH);
    digitalWrite(inputB, LOW);
    while(digitalRead(closed)==LOW){
		Serial.print(".");
        delay(50);
    }
    digitalWrite(inputA, LOW);
    digitalWrite(inputB, LOW);
    Serial.println("Chiusa!");
    MySQL_Cursor *cur_mem = new MySQL_Cursor(&conn);
    cur_mem->execute(INSERT_CLOSE);
    delete cur_mem;
    pushover.setSound("bike");
    pushover.setMessage("Chiusa");
    pushover.send();
    return true;
  }
  return false;
}
/*Open valve*/
boolean apri() {
  if ((digitalRead(opened) == LOW) && (digitalRead(closed) == HIGH)) {
    Serial.print("Sto aprendo");
    digitalWrite(inputA, LOW);
    digitalWrite(inputB, HIGH);
    while(digitalRead(opened)==LOW){
		Serial.print(".");
        delay(50);
    }
    digitalWrite(inputA, LOW);
    digitalWrite(inputB, LOW);
    Serial.println("Aperta!");
    MySQL_Cursor *cur_mem = new MySQL_Cursor(&conn);
    cur_mem->execute(INSERT_OPEN);
    delete cur_mem;
    pushover.setSound("bike");
    pushover.setMessage("Aperta");
    pushover.send();
    return true;
  }
  return false;
}
/*Save vars on EEPROM*/
void saveConf() {
  EEPROM.write(frequenza_addr, frequenza);
  EEPROM.write(ora_addr, ora);
  EEPROM.write(minuti_addr, minuti);
  EEPROM.write(duratab_addr, duratab);
  EEPROM.write(active_addr, active);
  EEPROM.write(daysCount_addr, daysCount);
  EEPROM.commit();
  Serial.println("Valori in memoria");
}
/*Get vars from EEPROM*/
void getConf() {
  frequenza = EEPROM.read(frequenza_addr);
  ora = EEPROM.read(ora_addr);
  minuti = EEPROM.read(minuti_addr);
  duratab = EEPROM.read(duratab_addr);
  active = EEPROM.read(active_addr);
  daysCount = EEPROM.read(daysCount_addr);
  ntpOffset=EEPROM.read(ntpOffset_addr);
  Serial.println("Valori prelevati dalla memoria");
}
/*Root of server*/
void handleRoot() {
  Serial.println("Enter handleRoot");
  String content = stato();
  server.send(200, "text/html", content);
}
/*Server Page to save data vars*/
void handleSave() {
  if ((server.hasArg("frequenza")) && server.hasArg("hash")) {
    String msg = server.arg("active") + server.arg("frequenza") + server.arg("ora") + server.arg("minuti") + server.arg("durata");
    char* cstr = new char[msg.length() + 1];
    strcpy(cstr, msg.c_str());
    if (server.arg("hash").equalsIgnoreCase(gen_hash(cstr))) {
      frequenza = server.arg("frequenza").toInt();
      ora = server.arg("ora").toInt();
      minuti = server.arg("minuti").toInt();
      duratab = server.arg("durata").toInt();
      active = server.arg("active").toInt();
      daysCount = 0;
      saveConf();
      DS3231AlarmOne alarm1(6, ora, minuti, 0, DS3231AlarmOneControl_HoursMinutesSecondsMatch);
      Rtc.SetAlarmOne(alarm1);
    } else {
      Serial.print("errore hash");
    }
    server.send(200, "text/html", "ok");
  } else {
    server.send(200, "text/html", "errore");
  }
}
/*Server Page to open valve manually*/
void handleOpen() {
  if ((stato() == "chiuso") && server.hasArg("hash")) {
    String msg = server.arg("rand");
    char* cstr = new char[msg.length() + 1];
    strcpy(cstr, msg.c_str());
    if (server.arg("hash").equalsIgnoreCase(gen_hash(cstr))) {
      durata = server.arg("durata").toInt();
      if (apri()) {
        String content = "aperto";
        alarm2CountManual = true;
        server.send(200, "text/html", content);
      } else {
        String content = "no";
        server.send(200, "text/html", content);
      }
    }
  } else {
    String content = "no";
    server.send(200, "text/html", content);
  }
}
/*Server Page to close valve manually */
void handleClose() {
  if ((stato() == "aperto") && server.hasArg("hash")) {
    String msg = server.arg("rand");
    char* cstr = new char[msg.length() + 1];
    strcpy(cstr, msg.c_str());
    if (server.arg("hash").equalsIgnoreCase(gen_hash(cstr))) {
      if (chiudi()) {
        String content = "chiuso";
        server.send(200, "text/html", content);
      } else {
        String content = "no";
        server.send(200, "text/html", content);
      }
    }
  } else {
    String content = "no";
    server.send(200, "text/html", content);
  }
}
/*Server page for 404 status*/
void handleNotFound() {
  String message = "File Not Found\n\n";
  server.send(404, "text/plain", message);
}
/*Server page to get temperature*/
void handleTemp() {
  /*Show Temp*/
  RtcTemperature temp = Rtc.GetTemperature();
  String message = (String) temp.AsFloat();
  server.send(200, "text/html", message);
}
/*Server page to get offset NTP*/
void handleNtp() {
  String message = (String) ntpOffset;
  server.send(200, "text/html", message);
}
/*Server page to save offset NTP*/
void handleSaveNtp(){
  if (server.hasArg("offset")&& server.hasArg("hash")){
    String msg = server.arg("offset");
    char* cstr = new char[msg.length() + 1];
    strcpy(cstr, msg.c_str());
    if (server.arg("hash").equalsIgnoreCase(gen_hash(cstr))) {
      ntpOffset=server.arg("offset").toInt();
      EEPROM.write(ntpOffset_addr, ntpOffset);
      EEPROM.commit();
      Serial.println("ntp aggiornato in memoria");
      timeClient.setTimeOffset(ntpOffset*3600);
      timeClient.update();
      Serial.println(timeClient.getFormattedTime());
      RtcDateTime compiled = timeClient.getEpochTime();
      Rtc.SetDateTime(compiled);
      server.send(200, "text/html", "ok");
    }
  }
}
/*Init vars, status, timers*/
void setup(void) {
  Serial.begin(57600);
  EEPROM.begin(7);
  /*Wifi*/
  WiFi.begin(ssid, password);
  Serial.println("");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected to ");
  Serial.println(ssid);
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  /*Set Pin Control*/
  pinMode(inputA, OUTPUT);
  pinMode(inputB, OUTPUT);
  pinMode(opened, INPUT);
  pinMode(closed, INPUT);
  pinMode(buttonClose, INPUT);
  pinMode(pinLedOpen, OUTPUT);
  getConf();
  /*NTP*/
  timeClient.begin();
  timeClient.setTimeOffset(ntpOffset*3600);
  timeClient.update();
  Serial.println(timeClient.getFormattedTime());
  /*RTC*/
  pinMode(RtcSquareWavePin, INPUT_PULLUP);
  Rtc.Begin();
  RtcDateTime compiled = timeClient.getEpochTime();
  if (!Rtc.IsDateTimeValid()) {
    Serial.println("RTC lost confidence in the DateTime!");
    Rtc.SetDateTime(compiled);
  }
  if (!Rtc.GetIsRunning()) {
    Serial.println("RTC was not actively running, starting now");
    Rtc.SetIsRunning(true);
  }
  RtcDateTime now = Rtc.GetDateTime();
  if (now < compiled) {
    Serial.println("RTC is older than compile time!  (Updating DateTime)");
    Rtc.SetDateTime(compiled);
  }
  Rtc.Enable32kHzPin(false);
  Rtc.SetSquareWavePin(DS3231SquareWavePin_ModeAlarmBoth);
  DS3231AlarmOne alarm1(6, ora, minuti, 0, DS3231AlarmOneControl_HoursMinutesSecondsMatch);
  Rtc.SetAlarmOne(alarm1);
  DS3231AlarmTwo alarm2(0, 0, 0, DS3231AlarmTwoControl_OncePerMinute);
  Rtc.SetAlarmTwo(alarm2);
  Rtc.LatchAlarmsTriggeredFlags();
  attachInterrupt(digitalPinToInterrupt(RtcSquareWavePin), InteruptServiceRoutine, FALLING);
  /*Set Server*/
  server.on("/", handleRoot);
  server.on("/open", handleOpen);
  server.on("/close", handleClose);
  server.on("/save", handleSave);
  server.on("/temp", handleTemp);
  server.on("/ntp", handleNtp);
  server.on("/ntpsave", handleSaveNtp);
  server.onNotFound(handleNotFound);
  const char * headerkeys[] = {"User-Agent", "Cookie"} ;
  size_t headerkeyssize = sizeof(headerkeys) / sizeof(char*);
  server.collectHeaders(headerkeys, headerkeyssize );
  server.begin();
  Serial.println("HTTP server started");
  /*MYSQL*/
  if (conn.connect(server_addr, 3306, user, pasw)) {
    delay(1000);
  } else {
    Serial.println("Connection failed.");
  }
  /*Check if valve is open at start, closes for security*/
  chiudi();
}
/*Call when there is a event on timer*/
void InteruptServiceRoutine() {
  DS3231AlarmFlag flag = Rtc.LatchAlarmsTriggeredFlags();
  if ((flag & DS3231AlarmFlag_Alarm1) && (active == 1)) {
    checkFreq = true;
  }
  if ((flag & DS3231AlarmFlag_Alarm2) && (alarm2Count == true)) {
    checkDurata = true;
  }
  if ((flag & DS3231AlarmFlag_Alarm2) && (alarm2CountManual == true)) {
    checkDurataManual = true;
  }
}
void loop(void) {
  server.handleClient();
  /*Turn on led if there is timer set*/
  if (active == 1) {
    digitalWrite(pinLedOpen, HIGH);
  } else {
    digitalWrite(pinLedOpen, LOW);
  }
  /*Close with button*/
  if (digitalRead(buttonClose) == HIGH) {
    chiudi();
  }
  /*Check if it is necessary to open*/
  if (checkFreq) {
    /*chose by recurrence*/
    switch (frequenza) {
      case 0: {
          Serial.println("Devo Aprire per una sola volta");
          alarm2Count = true;
          apri();
          active = 0;
          EEPROM.write(active_addr, active);
          EEPROM.commit();
          break;
        }
      case 12: {
          Serial.print("Devo Aprire ogni 12 ore, ");
          ora = (ora + 12) % 24;
          DS3231AlarmOne alarm1(6, ora, minuti, 0, DS3231AlarmOneControl_HoursMinutesSecondsMatch);
          Rtc.SetAlarmOne(alarm1);
          alarm2Count = true;
          Serial.print("prossima apertura alle ");
          Serial.println(ora);
          break;
        }
      case 24: {
          Serial.println("Devo Aprire ogni 24 ore");
          alarm2Count = true;
          break;
        }
      case 48: {
          if (daysCount == 0) {
            Serial.println("Devo Aprire ogni 48 ore, qui apro");
            alarm2Count = true;
          }
          if (daysCount < 2) {
            daysCount++;
            EEPROM.write(daysCount_addr, daysCount);
            EEPROM.commit();
          } else {
            Serial.println("Devo Aprire ogni 48 ore, qui apro");
            alarm2Count = true;
            daysCount = 1;
            EEPROM.write(daysCount_addr, daysCount);
          }
          break;
        }
      case 72: {
          if (daysCount == 0) {
            Serial.println("Devo Aprire ogni 72 ore, qui apro");
            alarm2Count = true;
          }
          if (daysCount < 3) {
            daysCount++;
            EEPROM.write(daysCount_addr, daysCount);
            EEPROM.commit();
          } else {
            Serial.println("Devo Aprire ogni 72 ore, qui apro");
            alarm2Count = true;
            daysCount = 1;
            EEPROM.write(daysCount_addr, daysCount);
            EEPROM.commit();
          }
          break;
        }
      case 168: {
          if (daysCount == 0) {
            Serial.println("Devo Aprire ogni 168 ore, qui apro");
            alarm2Count = true;
          }
          if (daysCount < 7) {
            daysCount++;
            EEPROM.write(daysCount_addr, daysCount);
          } else {
            Serial.println("Devo Aprire ogni 168 ore, qui apro");
            alarm2Count = true;
            daysCount = 1;
            EEPROM.write(daysCount_addr, daysCount);
          }
          break;
        }

    }
    checkFreq = false;
  }
  /*Check if it's time to close*/
  if (checkDurata) {
    if (interruptCountMinute < duratab - 1) {
      interruptCountMinute++;
    } else {
      interruptCountMinute = 0;
      alarm2Count = false;
      Serial.print("Sono passati ");
      Serial.print(duratab);
      Serial.println(" minuti, chiudo");
      chiudi();
    }
    checkDurata = false;
  }
  /*Check if it's time to close (manual open)*/
  if (checkDurataManual) {
    if (interruptCountMinuteManual < durata) {
      interruptCountMinuteManual++;
    } else {
      interruptCountMinuteManual = 0;
      durata = 0;
      alarm2CountManual = false;
      Serial.print("Sono passati ");
      Serial.print(durata);
      Serial.println(" minuti, chiudo");
      chiudi();
    }
    checkDurataManual = false;
  }
}

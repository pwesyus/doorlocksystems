#include <ESP8266WebServer.h>
#include <ESP8266HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Servo.h>
#include <TimeLib.h>
#include <ArduinoJson.h>
#include <ESP8266WiFi.h>
#include <vector>

#define RST_PIN D3
#define SS_PIN D4
MFRC522 mfrc522(SS_PIN, RST_PIN);

#define ON_Board_LED D0

LiquidCrystal_I2C lcd(0x27, 16, 2);
Servo servo;

const char* ssid = "HAA?";
const char* password = "09@Presyuscutie";

ESP8266WebServer server(80);
int readsuccess;
byte readcard[4];
char str[32] = "";
String StrUID;
String authorizedUser;
String authorizedName; // Store the authorized user (ID)
String userName;

WiFiClient client;

enum ServoState {
  LOCKED,
  UNLOCKED
};

bool doorLocked = true;
std::vector<String> registeredRFIDNumbers; // Store registered RFID numbers
std::vector<String> registeredNames;

ServoState servoState = LOCKED;
String firstTapUID; 
String firstTapUsername;

const int timeZoneOffset = 8;

void setup() {
  Serial.begin(115200); // Initialize the serial communication
  Wire.begin(D1, D2); // Initialize the I2C communication
  servo.attach(D8); // Attach the servo to pin D8

  lcd.init(); // Initialize the LCD
  lcd.backlight(); // Turn on the LCD backlight

  SPI.begin(); // Initialize SPI communication
  mfrc522.PCD_Init(); // Initialize the RFID reader

  delay(5000); // Delay for 5 seconds

  WiFi.begin(ssid, password); // Connect to the Wi-Fi network
  Serial.println("");

  pinMode(ON_Board_LED, OUTPUT);
  digitalWrite(ON_Board_LED, HIGH);

  delay(5000); // Delay for 5 seconds

  configTime(timeZoneOffset * 3600, 0, "pool.ntp.org"); // Configure time synchronization

  while (!time(nullptr)) {
    Serial.print(".");
    delay(1000);
  }

  lcd.clear(); // Clear the LCD screen
  lcd.setCursor(0, 0);
  lcd.print(" Scan your card ");
  lcd.setCursor(0, 1);
  lcd.print("     Here       ");

  Serial.print("Connecting");
  while (WiFi.status() != WL_CONNECTED) {
    Serial.print(".");
    digitalWrite(ON_Board_LED, LOW);
    delay(250);
    digitalWrite(ON_Board_LED, HIGH);
    delay(250);
  }
  digitalWrite(ON_Board_LED, HIGH);
  Serial.println("");
  Serial.print("Successfully connected to: ");
  Serial.println(ssid);
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  Serial.println("");

  servo.write(0); // Initialize the servo to the locked position
  servoState = LOCKED;

  // Retrieve authorized user (ID) and username from the schedule.php file
  retrieveAuthorizedUsersAndNames();
  
  
}

void loop() {
  readsuccess = getid(); // Check for RFID card presence and read its data

  if (readsuccess) {
    digitalWrite(ON_Board_LED, LOW); // Turn off the onboard LED

    time_t currentTime = now() + timeZoneOffset * SECS_PER_HOUR; // Get the current time
    char timestamp[20];
    sprintf(timestamp, "%04d-%02d-%02d %02d:%02d:%02d", year(currentTime), month(currentTime), day(currentTime), hour(currentTime), minute(currentTime), second(currentTime));

    lcd.clear(); // Clear the LCD screen
  
    if (checkIfAuthorizedUser(StrUID)) {
      userName = authorizedName.c_str();
      lcd.clear(); // Clear the LCD screen
      
      if (servoState == LOCKED) {
        servo.write(90); // Unlock the door (servo)
        servoState = UNLOCKED;
        
        lcd.setCursor(0, 0);
        lcd.print("      Door      ");
        lcd.setCursor(0, 1);
        lcd.print("    Unlocked    ");
        delay(5000);
        firstTapUID = StrUID;
        firstTapUsername = userName;
        lcd.clear(); // Clear the LCD screen
        lcd.setCursor(0, 0);
        lcd.print("Welcome, Ms./Mr.");
        lcd.setCursor(0, 1);
        lcd.print(userName);
        sendTimeData(timestamp, "getTimein.php", StrUID.c_str(), userName.c_str()); // Send time data for time-in
      } else if (servoState == UNLOCKED && StrUID == firstTapUID){
        servo.write(0); // Lock the door (servo)
        servoState = LOCKED;
        lcd.setCursor(0, 0);
        lcd.print("      Door      ");
        lcd.setCursor(0, 1);
        lcd.print("     Locked     ");
        delay(3000);
        
        lcd.clear(); // Clear the LCD screen
        lcd.setCursor(0, 0);
        lcd.print("Thankyou,Ms./Mr.");
        lcd.setCursor(0, 1);
        lcd.print(firstTapUsername);
        sendTimeData(timestamp, "getTimeout.php", StrUID.c_str(), userName.c_str()); // Send time data for time-in
        delay(2000);
        firstTapUID = "";
        firstTapUsername = "";
        lcd.clear(); // Clear the LCD screen
        lcd.setCursor(0, 0);
        lcd.print(" Scan your card ");
        lcd.setCursor(0, 1);
        lcd.print("     Here       ");
      } else if (servoState == UNLOCKED && StrUID != firstTapUID) {
        // The door is already unlocked by someone else
        lcd.setCursor(0, 0);
        lcd.print(" Door unlocked  ");
        lcd.setCursor(0, 1);
        lcd.print(" by other user  ");
        Serial.println("Unauthorized Access Attempt: " + StrUID);
        delay(4000);
        lcd.clear(); // Clear the LCD screen
        lcd.setCursor(0, 0);
        lcd.print("Welcome, Ms./Mr.");
        lcd.setCursor(0, 1);
        lcd.print(firstTapUsername);
      }
      else{
        lcd.setCursor(0, 0);
        lcd.print("  No Schedule!  ");
        Serial.println("Unauthorized Access Attempt: " + StrUID);
        delay(5000);
        lcd.clear(); // Clear the LCD screen
        lcd.setCursor(0, 0);
        lcd.print("Welcome, Ms./Mr.");
        lcd.setCursor(0, 1);
        lcd.print(userName);
    
      }
    } else {
        // User is not authorized
      lcd.setCursor(0, 0);
      lcd.print("  No Schedule!  ");
      Serial.println("Unauthorized Access Attempt: " + StrUID);
      delay(2000);
      lcd.clear(); // Clear the LCD screen
      lcd.setCursor(0, 0);
      lcd.print(" Scan your card ");
      lcd.setCursor(0, 1);
      lcd.print("     Here       ");
    }
      sendUIDAndUserData(StrUID.c_str(),  userName.c_str(), timestamp);
      digitalWrite(ON_Board_LED, HIGH); // Turn on the onboard LED
  }
}
int getid() {
  if (!mfrc522.PICC_IsNewCardPresent()) {
    return 0; // No new card present
  }
  if (!mfrc522.PICC_ReadCardSerial()) {
    return 0; // Error reading the card
  }

  Serial.print("THE UID OF THE SCANNED CARD IS: ");

  for (int i = 0; i < 4; i++) {
    readcard[i] = mfrc522.uid.uidByte[i];
    array_to_string(readcard, 4, str);
    StrUID = str;
  }
  mfrc522.PICC_HaltA();
  return 1; // Card read successfully
}

bool checkIfAuthorizedUser(const String& tappedRFID) {
  for (size_t i = 0; i < registeredRFIDNumbers.size(); i++) {
    if (tappedRFID == registeredRFIDNumbers[i]) {
      authorizedName = registeredNames[i];
      return true;
    }
  }
  return false;
}

void array_to_string(byte array[], unsigned int len, char buffer[]) {
  for (unsigned int i = 0; i < len; i++) {
    byte nib1 = (array[i] >> 4) & 0x0F;
    byte nib2 = (array[i] >> 0) & 0x0F;
    buffer[i * 2 + 0] = nib1 < 0xA ? '0' + nib1 : 'A' + nib1 - 0xA;
    buffer[i * 2 + 1] = nib2 < 0xA ? '0' + nib2 : 'A' + nib2 - 0xA;
  }
  buffer[len * 2] = '\0';
}


void retrieveAuthorizedUsersAndNames() {
  HTTPClient http;
  http.begin(client, "http://192.168.1.8/doorlocksystem/schedule.php");

  int httpCode = http.GET();
  if (httpCode == HTTP_CODE_OK) {
    String payload = http.getString();
    DynamicJsonDocument doc(1024);
    deserializeJson(doc, payload);


    // Access the data from the JSON response based on your PHP script
    JsonArray masterkeyUsers = doc["masterkey_users"];
    for (JsonVariant user : masterkeyUsers) {
      String id = user["id"].as<String>();
      String name = user["name"].as<String>();
      registeredRFIDNumbers.push_back(id);
      registeredNames.push_back(name);
    }

    JsonArray scheduledUsers = doc["users_with_schedules"];
    for (JsonVariant user : scheduledUsers) {
      String id = user["id"].as<String>();
      String name = user["name"].as<String>();
      registeredRFIDNumbers.push_back(id);
      registeredNames.push_back(name);
    }
  } else {
    Serial.println("Failed to retrieve authorized users and names");
    lcd.setCursor(0, 0);
    lcd.print("   Failed  to   ");
    lcd.setCursor(0, 1);
    lcd.print(" retrieve  user ");
  }

  http.end();
}



void sendUIDAndUserData(String UID, String username, const char* timestamp) {
  HTTPClient http;
  String serverAddress = "http://192.168.1.8/doorlocksystem/getUID.php";

  String postData = "UIDresult=" + UID + "&UserName=" + username + "&Timestamp=" + timestamp;

  http.begin(client, serverAddress);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  int httpCode = http.POST(postData);
  String payload = http.getString();

  Serial.println(UID);
  Serial.println(httpCode);
  Serial.println(payload);

  http.end();
}

void sendTimeData(const char* timestamp, const char* phpScript, const char* RFIDNumber, const char* userName) {
  HTTPClient http;
  String serverAddress = "http://192.168.1.8/doorlocksystem/" + String(phpScript);

  StaticJsonDocument<200> jsonPayload;
  jsonPayload["Timestamp"] = timestamp;
  jsonPayload["RFIDNumber"] = RFIDNumber;
  jsonPayload["UserName"] = userName; // Include the userName in the JSON data

  String postData;
  serializeJson(jsonPayload, postData);

  http.begin(client, serverAddress);
  http.addHeader("Content-Type", "application/json");

  int httpCode = http.POST(postData);
  String payload = http.getString();

  Serial.println(httpCode);
  Serial.println(payload);

  http.end();
}

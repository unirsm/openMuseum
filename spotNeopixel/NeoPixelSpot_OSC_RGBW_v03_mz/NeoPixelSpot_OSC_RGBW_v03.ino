/*---------------------------------------------------------------------------------------------
  NeoPixel RGBW Spot v0.3
  M.Piccin 03/2019 for CaseMuseo project
  
  Su D1 R2 & mini board
  Controlled by Open Sound Control (OSC)
  --------------------------------------------------------------------------------------------- */
#ifdef ESP8266
#include <ESP8266WiFi.h>
#else
#include <  >
#endif
#include <WiFiUdp.h>
#include <OSCMessage.h>
#include <OSCBundle.h>
#include <OSCData.h>
#include <Adafruit_NeoPixel.h>

#define PIN            D4
#define NUMPIXELS      16

char ssid[] = "CCM";          // your network SSID (name)
char pass[] = "casamuseo";          // your network password

IPAddress local_IP(192, 168, 1, 44);
IPAddress gateway(192, 168, 1, 1);
IPAddress subnet(255, 255, 255, 0);
IPAddress primaryDNS(8, 8, 8, 8); //optional
IPAddress secondaryDNS(192, 168, 1, 1); //optional

// A UDP instance to let us send and receive packets over UDP
WiFiUDP Udp;
const unsigned int localPort = 8889;        // local port to listen for UDP packets (here's where we send the packets)

Adafruit_NeoPixel pixels = Adafruit_NeoPixel(NUMPIXELS, PIN, NEO_GRBW + NEO_KHZ800);

OSCErrorCode error;

void setup() {
  Serial.begin(115200);

  setup_wifi();

  pixels.begin();
  for (int i = 0; i < NUMPIXELS; i++) {
    pixels.setPixelColor(i, pixels.Color(0, 0, 0, 0));
    pixels.show();
    delay(10);
  }
}


void neopixelspot(OSCMessage &msg) {
  int length = msg.getDataLength(0);
  char msg_str[length];
  msg.getString(0, msg_str, length);

  byte redval   = fromhex (& msg_str [1]);
  byte greenval = fromhex (& msg_str [3]);
  byte blueval  = fromhex (& msg_str [5]);
  byte whiteval = fromhex (& msg_str [7]);

  /*
    Serial.print(msg_str);
    Serial.print("-");
    Serial.print(redval);
    Serial.print("-");
    Serial.print(greenval);
    Serial.print("-");
    Serial.print(blueval);
    Serial.print("-");
    Serial.println(whiteval);
  */

  for (int i = 0; i < NUMPIXELS; i++) {
    pixels.setPixelColor(i, pixels.Color((int) redval, (int) greenval,  (int) blueval,  (int) whiteval));
    pixels.show();
    delay(10);
  }

}

void loop() {
  OSCMessage msg;
  int size = Udp.parsePacket();

  if (size > 0) {
    while (size--) {
      msg.fill(Udp.read());
    }
    if (!msg.hasError()) {
      msg.dispatch("/neopixelspot", neopixelspot);
    } else {
      error = msg.getError();
      Serial.print("error: ");
      Serial.println(error);
    }
  }
}

void setup_wifi() {

  delay(10);

  //clean up AP mode and force STA mode
  //https://stackoverflow.com/questions/39688410/how-to-switch-to-normal-wifi-mode-to-access-point-mode-esp8266
  WiFi.softAPdisconnect();
  WiFi.disconnect();
  WiFi.mode(WIFI_STA);
  delay(100);

  // We start by connecting to a WiFi network
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  if (!WiFi.config(local_IP, gateway, subnet, primaryDNS, secondaryDNS)) {
    Serial.println("STA Failed to configure");
  }

  WiFi.begin(ssid, pass);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());

  Serial.println("Starting UDP");
  Udp.begin(localPort);
}


byte fromhex (const char * str) {
  char c = str [0] - '0';
  if (c > 9) {
    c -= 7;
  }
  int result = c;
  c = str [1] - '0';
  if (c > 9) {
    c -= 7;
  }
  return (result << 4) | c;
}

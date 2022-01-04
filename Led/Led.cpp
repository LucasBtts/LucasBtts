#include "Arduino.h"
#include "Led.h"
Led::Led(int pin){
    pinMode(pin, OUTPUT);
    _pin = pin;
}
void Led::ledOn(){
    digitalWrite(_pin, HIGH);
}
void Led::ledOff(){
    digitalWrite(_pin, LOW);
}
void Led::ledClignotant(int T,int N)
{
    for (int i = 0; i <= N ; i++) {
    ledOn();
    delay(T/2);
    ledOff();
    delay(T/2);

  }
}
void Led::ledChange()
{
    getEtat();
    bool val =!val;
    digitalWrite(_pin, val);
}
bool Led::getEtat()
{
     return digitalRead(_pin);
}
#ifndef Led_h  
#define Led_h
#include "Arduino.h"

class Led
{
    public:
        Led(int pin);
        void ledOn();
        void ledOff();
        void ledClignotant(int T,int N);
        void ledChange();
        bool getEtat();
    private:
        int _pin;
};

#endif
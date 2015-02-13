library somePackage;
import 'package:unittest/unittest.dart';
import 'package:unittest/html_config.dart';
import 'package:part/core.dart';

void main() {
  useHtmlConfiguration();
  test('parseNumber', () {
    expect(123,parseNumber("123px"));
    expect(123,parseNumber("123"));
    expect(123,parseNumber("123asd"));
    expect(123,parseNumber(" 1asd23asd"));
  });
  test('linearAnimationFunction', (){
    expect(0, linearAnimationFunction(0,0,100));
    expect(25, linearAnimationFunction(0.25,0,100));
    expect(50, linearAnimationFunction(0.5,0,100));
    expect(75, linearAnimationFunction(0.75,0,100));
    expect(100, linearAnimationFunction(1,0,100));
  });

  test('sizeToString', (){
    expect(sizeToString(0), "0 B");
    expect(sizeToString(10), "10 B");
    expect(sizeToString(102), "102 B");
    expect(sizeToString(103), "0.10 KB");
    expect(sizeToString(1024), "1.0 KB");
    expect(sizeToString(1024*10), "10.0 KB");
    expect(sizeToString(1024*102), "102.0 KB");
    expect(sizeToString(1024*103), "0.10 MB");
    expect(sizeToString(1024*1024), "1.0 MB");
    expect(sizeToString(1024*1024*10), "10.0 MB");
    expect(sizeToString(1024*1024*102), "102.0 MB");
    expect(sizeToString(1024*1024*103), "103.0 MB");
    expect(sizeToString(1024*1024*1024), "1024.0 MB");
    expect(sizeToString(1024*1024*1024*10), "10240.0 MB");
  });

  test('validMail', (){
    expect(validMail("test"), false);
    expect(validMail("test@"), false);
    expect(validMail("test@test"), false);
    expect(validMail("test@test.d"), false);
    expect(validMail("test@.d"), false);
    expect(validMail("test@.dk"), false);
    expect(validMail("test@te.dk"), true);
    expect(validMail("test@te.dkass"), false);
    expect(validMail("test@tetest.co.uk"), true);
    expect(validMail("@te.dk"), false);
  });

  test('validUrl', (){
    expect(validUrl(""), false);
    expect(validUrl("asd"), false);
    expect(validUrl("http://asd"), false);
    expect(validUrl("http://asd.dk"), true);
    expect(validUrl("https://asd.dk"), true);
    expect(validUrl("ftp://asd.dk"), false);
    expect(validUrl("http://åsd.dk"), true);
    expect(validUrl("http://æøå.dks"), true);
    expect(validUrl("https://åsd.asdd"), false);
    expect(validUrl("https://åsd.asd/asd"), false);
  });

}
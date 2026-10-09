unsigned long tempoAnterior = 0;
const unsigned long intervalo = 500;

void verificarLuz()
{
  int leitura = analogRead(A0);

  int porcentagem = map(leitura, 54, 974, 0, 100);
  Serial.print("Luz: ");
  Serial.print(leitura);
  Serial.println("%");

  if (porcentagem >= 50)
  {
    digitalWrite(8, HIGH);
  }
  else
  {
    digitalWrite(8, LOW);
  }
}

void atualizarSensor()
{
  if (millis() - tempoAnterior >= intervalo)
  {
    tempoAnterior = millis();
    verificarLuz();
  }
}

void setup()
{
  pinMode(A0, INPUT);
  pinMode(8, OUTPUT);
  Serial.begin(9600);
}

void loop()
{
  atualizarSensor();
}
# Testovací studentský účet

Testovací účet vytvářej až na konkrétním serveru, aby v distribučním ZIPu nebylo veřejně známé heslo.

## 1.A — doporučený test

```bash
cd /var/www/clients/client10/web9/web/sub/w
php tools/create_test_student.php \
  --class=class_1a \
  --email=demo.student@educanet.cz \
  --name='Testovací student'
```

Nástroj vypíše náhodné heslo. Účet je rovnou ověřený a svázaný s 1.A, takže po přihlášení vidí pouze obsah a kalendář 1.A.

### Vlastní heslo

```bash
php tools/create_test_student.php \
  --class=class_1a \
  --email=demo.student@educanet.cz \
  --name='Testovací student' \
  --password='TVE_SILNE_TESTOVACI_HESLO'
```

## Test jiné třídy

Změň pouze `--class` na `class_2a`, `class_3a` nebo `class_4a` a použij jiný e-mail.

## Odstranění

```bash
php tools/create_test_student.php --email=demo.student@educanet.cz --remove
```

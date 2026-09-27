# Private configuration template

`educanet.secrets.php` v této složce je pouze distribuční template s placeholderem.

Na aaPanelu jej **nenechávej jako produkční secret ve web rootu**. Použij:

```bash
bash tools/install_teacher_secret.sh
```

Výsledný secret bude vytvořen v:

```text
/www/server/educanet/educanet.secrets.php
```

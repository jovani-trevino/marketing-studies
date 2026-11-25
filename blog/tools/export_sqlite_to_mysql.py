import sqlite3
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SQLITE_PATH = ROOT / "storage" / "blog.sqlite"
SCHEMA_PATH = ROOT / "database" / "mysql_schema.sql"
OUTPUT_PATH = ROOT / "database" / "mysql_full_import.sql"

TABLES = [
    "users",
    "settings",
    "categories",
    "authors",
    "posts",
    "post_translations",
    "comments",
    "newsletter_subscribers",
    "newsletter_campaigns",
    "contact_messages",
    "lab_resources",
]


def mysql_quote(value):
    if value is None:
        return "NULL"
    if isinstance(value, (int, float)):
        return str(value)
    value = str(value)
    return "'" + value.replace("\\", "\\\\").replace("'", "\\'").replace("\x00", "") + "'"


def main():
    conn = sqlite3.connect(SQLITE_PATH)
    conn.row_factory = sqlite3.Row
    parts = [SCHEMA_PATH.read_text(encoding="utf-8"), "\nSET FOREIGN_KEY_CHECKS = 0;\n"]

    for table in TABLES:
        exists = conn.execute("SELECT name FROM sqlite_master WHERE type='table' AND name=?", (table,)).fetchone()
        if not exists:
            continue
        rows = conn.execute(f"SELECT * FROM {table}").fetchall()
        if not rows:
            continue
        columns = rows[0].keys()
        quoted_columns = ", ".join(f"`{column}`" for column in columns)
        parts.append(f"\nLOCK TABLES `{table}` WRITE;\n")
        for row in rows:
            values = ", ".join(mysql_quote(row[column]) for column in columns)
            parts.append(f"INSERT INTO `{table}` ({quoted_columns}) VALUES ({values});\n")
        parts.append("UNLOCK TABLES;\n")

    parts.append("\nSET FOREIGN_KEY_CHECKS = 1;\n")
    OUTPUT_PATH.write_text("".join(parts), encoding="utf-8")
    print(OUTPUT_PATH)


if __name__ == "__main__":
    main()

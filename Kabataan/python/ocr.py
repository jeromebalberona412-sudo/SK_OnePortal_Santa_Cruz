#!/usr/bin/env python3
"""Extract text from Philippine ID images using PaddleOCR (preferred) or EasyOCR fallback."""

from __future__ import annotations

import json
import os
import re
import sys


def emit(payload: dict, exit_code: int = 0) -> None:
    print(json.dumps(payload, ensure_ascii=False))
    sys.exit(exit_code)


def parse_paddle_result(result) -> tuple[list[dict], list[float]]:
    lines: list[dict] = []
    confidences: list[float] = []

    if not result:
        return lines, confidences

    for block in result:
        if not block:
            continue
        for item in block:
            if not item or len(item) < 2:
                continue
            text = str(item[1][0]).strip()
            conf = float(item[1][1])
            if text:
                lines.append({"text": text, "confidence": conf})
                confidences.append(conf)

    return lines, confidences


def parse_easyocr_result(result) -> tuple[list[dict], list[float]]:
    lines: list[dict] = []
    confidences: list[float] = []

    for item in result or []:
        if not item or len(item) < 2:
            continue
        text = str(item[1]).strip()
        conf = float(item[2]) if len(item) > 2 else 0.75
        if text:
            lines.append({"text": text, "confidence": conf})
            confidences.append(conf)

    return lines, confidences


def looks_like_address(text: str) -> bool:
    return bool(
        re.search(
            r"\b(sitio|purok|zone|brgy|barangay|sta\.?|santa\s*cruz|laguna|lag\.?|school|high)\b",
            text,
            re.I,
        )
    )


def run_paddle(image_path: str) -> tuple[list[dict], list[float], str]:
    from paddleocr import PaddleOCR

    ocr = PaddleOCR(use_angle_cls=True, lang="en", show_log=False)
    result = ocr.ocr(image_path, cls=True)
    lines, confidences = parse_paddle_result(result)

    return lines, confidences, "paddleocr"


def run_easyocr(image_path: str) -> tuple[list[dict], list[float], str]:
    import easyocr

    reader = easyocr.Reader(["en"], gpu=False, verbose=False)
    result = reader.readtext(image_path)
    lines, confidences = parse_easyocr_result(result)

    return lines, confidences, "easyocr"


def main() -> None:
    if len(sys.argv) < 2:
        emit({"success": False, "message": "Image path is required."}, 1)

    image_path = sys.argv[1]

    if not os.path.isfile(image_path):
        emit({"success": False, "message": "Image file not found."}, 1)

    lines: list[dict] = []
    confidences: list[float] = []
    engine = ""
    errors: list[str] = []

    for runner, label in ((run_paddle, "paddleocr"), (run_easyocr, "easyocr")):
        try:
            lines, confidences, engine = runner(image_path)
            if lines:
                break
        except ImportError as exc:
            errors.append(f"{label}: {exc}")
        except Exception as exc:  # noqa: BLE001
            errors.append(f"{label}: {exc}")

    if not lines:
        message = "PaddleOCR is not installed in the Python environment."
        if errors:
            message = "; ".join(errors)
        emit({"success": False, "message": message}, 1)

    full_text = " ".join(line["text"] for line in lines)
    average_confidence = sum(confidences) / len(confidences)
    has_address = looks_like_address(full_text)

    payload = {
        "success": True,
        "average_confidence": round(average_confidence, 3),
        "lines": lines,
        "full_text": full_text,
        "has_address_hint": has_address,
        "engine": engine,
    }

    emit(payload)


if __name__ == "__main__":
    main()

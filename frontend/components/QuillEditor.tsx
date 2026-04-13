"use client";

import { useEffect, useRef, useState } from "react";

type QuillModule = typeof import("quill");
type QuillInstance = import("quill").default;

const toolbarOptions = [
  [{ header: [1, 2, 3, false] }],
  ["bold", "italic", "underline"],
  [{ color: [] }, { background: [] }],
  [{ list: "ordered" }, { list: "bullet" }],
  [{ align: [] }],
  ["link", "image"],
  ["clean"],
];

export default function QuillEditor({
  value,
  onChange,
  placeholder,
}: {
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
}) {
  const hostRef = useRef<HTMLDivElement | null>(null);
  const editorRef = useRef<HTMLDivElement | null>(null);
  const quillRef = useRef<QuillInstance | null>(null);
  const onChangeRef = useRef(onChange);
  const lastHtmlRef = useRef(value);
  const [isReady, setIsReady] = useState(false);

  onChangeRef.current = onChange;

  useEffect(() => {
    let mounted = true;

    const setup = async () => {
      if (!hostRef.current || quillRef.current) {
        return;
      }

      const quillImport = await import("quill");
      const Quill = quillImport.default as QuillModule["default"];

      if (!mounted || !hostRef.current) {
        return;
      }

      const editorElement = document.createElement("div");
      hostRef.current.innerHTML = "";
      hostRef.current.appendChild(editorElement);
      editorRef.current = editorElement;

      const quill = new Quill(editorElement, {
        theme: "snow",
        placeholder,
        modules: {
          toolbar: toolbarOptions,
        },
      });

      if (value) {
        quill.clipboard.dangerouslyPasteHTML(value);
      }
      lastHtmlRef.current = quill.root.innerHTML === "<p><br></p>" ? "" : quill.root.innerHTML;

      quill.on("text-change", () => {
        const html = quill.root.innerHTML === "<p><br></p>" ? "" : quill.root.innerHTML;
        lastHtmlRef.current = html;
        onChangeRef.current(html);
      });

      quillRef.current = quill;
      setIsReady(true);
    };

    void setup();

    return () => {
      mounted = false;
      quillRef.current = null;
      editorRef.current = null;
      if (hostRef.current) {
        hostRef.current.innerHTML = "";
      }
    };
  }, [placeholder]);

  useEffect(() => {
    const quill = quillRef.current;
    if (!quill) {
      return;
    }

    if (value !== lastHtmlRef.current) {
      const selection = quill.getSelection();
      quill.clipboard.dangerouslyPasteHTML(value || "");
      lastHtmlRef.current = quill.root.innerHTML === "<p><br></p>" ? "" : quill.root.innerHTML;
      if (selection) {
        quill.setSelection(selection);
      }
    }
  }, [value]);

  return (
    <div className="overflow-hidden rounded-xl border border-gray-300 bg-white transition hover:border-gray-400 focus-within:border-[#0b63ce]">
      <div ref={hostRef} />
      {!isReady ? (
        <div className="space-y-3 p-4">
          <div className="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
            {Array.from({ length: 10 }).map((_, index) => (
              <span key={index} className="h-8 w-8 animate-pulse rounded bg-gray-100" />
            ))}
          </div>
          <div className="h-[280px] animate-pulse rounded-lg bg-gray-50" />
        </div>
      ) : null}
    </div>
  );
}

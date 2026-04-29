import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str } from "@/lib/sections/component-registry";

/** Spacer — type: "spacer", all variations. */
export function SpacerSection({ section }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;

  // Accept number, string "80", or string "80px" / "5rem"
  const raw    = str(c, "height", "80");
  const height = /^\d+$/.test(raw) ? `${raw}px` : raw || "80px";

  return <div className="spacer" style={{ height }} aria-hidden="true" />;
}

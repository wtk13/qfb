# New Feedback for {{ $businessName }}

@if($triage)
**Category:** {{ ucfirst(str_replace('_', ' ', $triage->category)) }}
**Urgency:** {{ ucfirst($triage->urgency) }}

---
@endif

**Customer said:**
> {{ $comment }}

@if($triage)
---

**Suggested response:**
{{ $triage->suggested_response }}
@endif

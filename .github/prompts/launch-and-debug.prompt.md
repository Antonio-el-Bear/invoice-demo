---
name: launch-and-debug
argument-hint: "What should be launched or debugged? (e.g., file, function, scenario)"
description: "Launches a program or script and guides the user through deep debugging, including step-by-step execution, breakpoints, and variable inspection. Use when: you want to start a process and perform detailed debugging, or need to diagnose complex issues interactively."
agent: agent
---

# Launch and Deep Debug Prompt

## Purpose
This prompt helps you launch a program, script, or scenario and guides you through deep debugging. It supports step-by-step execution, setting breakpoints, inspecting variables, and diagnosing complex issues interactively.

## Usage
- Specify what to launch (file, function, scenario, etc.)
- Optionally provide arguments or context for the launch
- The agent will:
  1. Start the specified process
  2. Guide you through setting breakpoints and stepping through code
  3. Help inspect variables and call stacks
  4. Assist in diagnosing and resolving issues

## Example Invocations
- "/launch-and-debug main.php"
- "/launch-and-debug invoice-create.php with POST data X"
- "/launch-and-debug function processInvoice() in invoice.php"

## Output
- Step-by-step debugging instructions
- Suggestions for breakpoints and inspection points
- Diagnostic insights and troubleshooting tips

---

<?php
/**
 * Demo articles — Software and Security desks.
 *
 * @package TechScope
 */

return array(

	array(
		'slug'     => 'rust-in-the-kernel',
		'title'    => 'Rust in the kernel stopped being an experiment and started being a maintenance question',
		'category' => 'software',
		'tags'     => array( 'rust', 'kernel', 'memory-safety', 'maintenance' ),
		'excerpt'  => 'The language landed. The harder arguments are about who reviews the bindings, and what happens when a C interface changes underneath them.',
		'days_ago' => 4,
		'blocks'   => array(
			array( 'p' => 'The question of whether Rust belongs in the Linux kernel has been settled by the ordinary mechanism: drivers written in it are merged, shipped and running in production. The argument that remains is about sustainability, and it is a more interesting argument than the one it replaced.' ),
			array( 'h2' => 'The abstraction layer is the project' ),
			array( 'p' => 'Rust kernel code does not call C kernel APIs directly. It calls safe abstractions that wrap them, and those abstractions encode invariants the C interface only documents in comments — if it documents them at all. Writing one requires understanding the C side deeply enough to know what is actually guaranteed.' ),
			array( 'quote' => 'Every safe abstraction is a claim about a C interface. When the C side changes, the claim has to be re-checked by someone who understands both.' ),
			array( 'p' => 'That is the maintenance burden in one sentence, and it does not go away as the port matures. It grows with the surface area.' ),
			array( 'h2' => 'What the safety actually buys' ),
			array( 'p' => 'The classes of bug that dominate kernel CVEs — use-after-free, double-free, buffer overrun, data races on shared state — are exactly the classes the type system rules out in safe code. Not reduces. Rules out, for code that does not use unsafe blocks.' ),
			array(
				'list' => array(
					'Use-after-free: prevented by ownership and borrow checking.',
					'Data races: prevented by the Send and Sync traits.',
					'Buffer overruns: prevented by bounds-checked slices.',
					'Logic errors, deadlocks, and incorrect hardware programming: entirely unaffected.',
				),
			),
			array( 'p' => 'That last line matters. A Rust driver can still hang a machine, corrupt a device, or deadlock. The guarantee is narrow and real, not broad and vague.' ),
			array( 'h2' => 'Review capacity' ),
			array( 'p' => 'The scarce resource is reviewers who are fluent in both languages and in the subsystem being wrapped. That intersection is small and does not scale quickly, which is why the sensible projects have grown their Rust surface deliberately rather than opportunistically.' ),
			array( 'h2' => 'Where it goes' ),
			array( 'p' => 'The trajectory most maintainers describe is new leaf drivers first, then self-contained subsystems, with core infrastructure staying in C for the foreseeable future. Not because rewriting it is impossible, but because the rewrite would consume exactly the review capacity that the new code needs.' ),
		),
	),

	array(
		'slug'     => 'build-systems-regression',
		'title'    => 'Build systems got faster and reproducibility got worse',
		'category' => 'software',
		'tags'     => array( 'build', 'reproducibility', 'ci', 'caching' ),
		'excerpt'  => 'Remote caching made builds fast by assuming inputs are fully declared. They almost never are, and the failure mode is a cache hit that should have been a miss.',
		'days_ago' => 7,
		'blocks'   => array(
			array( 'p' => 'Remote build caching is one of the clearest wins in modern engineering infrastructure. Hash the inputs of a build action, look up the hash, and if someone has already produced that output, download it instead of recomputing it. Build times drop by an order of magnitude.' ),
			array( 'h2' => 'The assumption underneath' ),
			array( 'p' => 'The scheme is correct if and only if the hash covers every input that can affect the output. Miss one and the cache returns a stale artefact that looks valid, passes its checksum, and is wrong.' ),
			array( 'quote' => 'A cache miss costs you a rebuild. An incorrect cache hit costs you a week and a production incident.' ),
			array( 'p' => 'The inputs that get missed are predictable, which is mildly encouraging:' ),
			array(
				'list' => array(
					'Environment variables read by a compiler plugin that the build graph does not know about.',
					'The system clock, embedded into a binary as a build timestamp.',
					'Absolute paths baked into debug information.',
					'Locale and timezone, which change string sorting in generated code.',
					'Network access during the build — the single largest source of undeclared input.',
				),
			),
			array( 'h2' => 'Sandboxing as the fix' ),
			array( 'p' => 'The durable answer is execution sandboxing: run each action with only its declared inputs visible, no network, a fixed clock and a normalised environment. Undeclared inputs then fail loudly at build time rather than silently at cache-lookup time.' ),
			array( 'p' => 'Teams that enable sandboxing on an existing codebase describe the same first week — a long list of actions that were quietly depending on something they never declared. That list is the pre-existing bug count, surfaced.' ),
			array( 'h2' => 'Verifying the invariant' ),
			array( 'p' => 'The cheap check is a rebuild comparison: build the same commit twice in deliberately different environments and compare outputs byte for byte.' ),
			array(
				'code' => "# Two builds, different paths, different clocks, same commit.\nbuild --output_base=/tmp/a //...\nbuild --output_base=/tmp/b //...\n\ndiffoscope /tmp/a/bin/service /tmp/b/bin/service",
			),
			array( 'p' => 'Run it nightly. It is the only thing that actually tells you whether the caching layer is safe, and it fails long before anyone would otherwise notice.' ),
		),
	),

	array(
		'slug'     => 'package-registry-trust',
		'title'    => 'Package registries are infrastructure, and they are funded like hobbies',
		'category' => 'software',
		'tags'     => array( 'registries', 'open-source', 'funding', 'supply-chain' ),
		'excerpt'  => 'Every language ecosystem depends on a registry that most of its users could not name the operator of. The resourcing gap is structural, not incidental.',
		'days_ago' => 12,
		'blocks'   => array(
			array( 'p' => 'A package registry is the most load-bearing piece of software in a language ecosystem. It is also, in most ecosystems, operated on a budget that would not cover a mid-size engineering team, and staffed partly by volunteers.' ),
			array( 'h2' => 'What a registry is actually responsible for' ),
			array( 'p' => 'The naive view is file hosting. The actual job list is longer: immutability guarantees, name-squatting disputes, malware scanning, account recovery for maintainers who lose credentials, legal takedowns, and the archival responsibility of serving a ten-year-old version that one critical build still pins.' ),
			array( 'quote' => 'Serving the current version is the easy half. Serving every version forever, correctly, is the commitment.' ),
			array( 'h2' => 'The attack surface' ),
			array( 'p' => 'Registries are high-value targets for obvious reasons, and the attack patterns are well catalogued:' ),
			array(
				'list' => array(
					'<strong>Typosquatting</strong> — a package one character off a popular name.',
					'<strong>Maintainer account takeover</strong> — still, overwhelmingly, the highest-yield attack.',
					'<strong>Dependency confusion</strong> — an internal package name claimed publicly, so the resolver prefers the attacker.',
					'<strong>Post-publish mutation</strong> — fixed in most ecosystems by immutability, but not all.',
				),
			),
			array( 'p' => 'Mandatory two-factor authentication for high-download-count maintainers closed most of the account-takeover path where it was adopted, which is the strongest evidence available that resourcing these operations produces measurable security.' ),
			array( 'h2' => 'Signing helps less than advertised' ),
			array( 'p' => 'Artifact signing establishes that a package came from a particular identity. It does not establish that the identity is trustworthy, and it does not help when the maintainer account itself is compromised — the attacker signs with the real key.' ),
			array( 'p' => 'What signing does enable is after-the-fact accountability and transparency logs that make silent substitution detectable. Both are worth having. Neither is the perimeter they are sometimes sold as.' ),
			array( 'h2' => 'The funding shape' ),
			array( 'p' => 'The pattern that has worked is boring: a foundation with corporate members, a small permanent staff, and infrastructure donated at cost by providers with an interest in the ecosystem surviving. It is not elegant, and it depends on continued goodwill, which is an uncomfortable thing for critical infrastructure to depend on.' ),
		),
	),

	array(
		'slug'     => 'supply-chain-sbom',
		'title'    => 'SBOMs arrived. Almost nobody is reading them',
		'category' => 'security',
		'tags'     => array( 'sbom', 'supply-chain', 'compliance', 'vulnerability-management' ),
		'excerpt'  => 'Generating a software bill of materials is now routine. Acting on one is still mostly manual, and the gap between those two facts is where the risk lives.',
		'days_ago' => 8,
		'blocks'   => array(
			array( 'p' => 'Software bills of materials went from proposal to procurement requirement in a few short years. Most build pipelines now emit one. The question that matters is what happens to the file afterwards, and the honest answer at most organisations is: it is stored, and then nothing.' ),
			array( 'h2' => 'Generation is solved' ),
			array( 'p' => 'Build-integrated generation produces accurate output because the build system already knows the dependency graph. Post-hoc scanning of a built artefact is less accurate but works on things you did not build. Both are available, both are cheap to run.' ),
			array( 'quote' => 'An SBOM nobody queries is a compliance artefact. An SBOM wired into alerting is a control.' ),
			array( 'h2' => 'The missing half' ),
			array( 'p' => 'To act on an SBOM, three systems have to be connected: an inventory of what is deployed where, a feed of newly disclosed vulnerabilities, and a mapping between package identifiers in the SBOM and identifiers in the vulnerability feed. The third one is where it falls apart.' ),
			array(
				'list' => array(
					'Package naming differs between ecosystems and between databases describing the same ecosystem.',
					'Version ranges in advisories are often imprecise, listing "before 2.4.1" when the fix landed in a backport.',
					'Vendored and statically linked dependencies frequently appear under no identifier at all.',
				),
			),
			array( 'h2' => 'Reachability is the other half' ),
			array( 'p' => 'A dependency being present is not the same as a vulnerability being exploitable. Reachability analysis — does any code path actually reach the vulnerable function — cuts alert volume dramatically, often by most of it.' ),
			array( 'p' => 'It also introduces a false-negative risk that security teams are right to be careful about, because reflection, dynamic dispatch and configuration-driven code paths defeat static reachability in ways that are hard to bound.' ),
			array( 'h2' => 'What good looks like' ),
			array( 'p' => 'The organisations getting value from this describe a specific pipeline: SBOM emitted at build, stored keyed by artefact digest, joined nightly against an advisory feed, filtered by deployment inventory so only running versions alert, and routed to the team that owns the service rather than to a central queue.' ),
			array( 'p' => 'None of that is novel technology. It is integration work, and it is the part that procurement mandates did not require and therefore did not fund.' ),
		),
	),

	array(
		'slug'     => 'post-quantum-migration',
		'title'    => 'The post-quantum migration is a certificate-lifecycle problem in disguise',
		'category' => 'security',
		'tags'     => array( 'cryptography', 'post-quantum', 'tls', 'pki' ),
		'excerpt'  => 'The algorithms are standardised and shipping. What is blocking deployment is that most organisations cannot enumerate their own certificates.',
		'days_ago' => 11,
		'blocks'   => array(
			array( 'p' => 'The standardised post-quantum key encapsulation and signature algorithms are implemented in the major TLS libraries, and hybrid key exchange is already carrying a substantial share of real web traffic. The cryptography is, for practical purposes, ready.' ),
			array( 'h2' => 'Harvest now, decrypt later' ),
			array( 'p' => 'The urgency for key exchange comes from the recording threat: traffic captured today can be decrypted whenever a sufficiently capable quantum computer exists. Anything with a confidentiality requirement measured in decades is exposed now, not later.' ),
			array( 'quote' => 'Key exchange is urgent because the attack is retroactive. Signatures are not, because a forged signature in 2041 does not help an attacker in 2026.' ),
			array( 'p' => 'This asymmetry is why hybrid key exchange deployed quickly and post-quantum certificates have not. The incentives genuinely differ.' ),
			array( 'h2' => 'Why signatures are harder anyway' ),
			array( 'p' => 'Post-quantum signatures are large. A certificate chain that comfortably fit in a few kilobytes grows substantially, and TLS handshakes that previously completed in a small number of packets no longer do. On lossy networks that costs round trips, and the effect is measurable.' ),
			array(
				'list' => array(
					'Larger certificates push handshakes past initial congestion windows.',
					'Embedded devices with fixed buffer sizes reject chains they cannot store.',
					'Middleboxes with hardcoded size assumptions fail in ways that are hard to diagnose.',
				),
			),
			array( 'h2' => 'The actual blocker' ),
			array( 'p' => 'Ask an enterprise security team to list every certificate their organisation uses, including the ones embedded in appliances, pinned in mobile applications, and issued by a forgotten internal CA, and the exercise usually takes months. Migration cannot be planned against an inventory that does not exist.' ),
			array( 'h2' => 'Crypto-agility, meant seriously' ),
			array( 'p' => 'The lasting lesson is not "adopt these algorithms". It is that algorithm choice should be a configuration decision with a short change cycle, not an architectural assumption baked into a protocol implementation. Organisations that invested in short-lived certificates and automated issuance are finding this migration straightforward. The ones with ten-year certificates in appliances are not.' ),
		),
	),

	array(
		'slug'     => 'memory-safety-cve-decline',
		'title'    => 'Memory-safety CVEs are falling, and the reason is unglamorous',
		'category' => 'security',
		'tags'     => array( 'memory-safety', 'cve', 'compilers', 'hardening' ),
		'excerpt'  => 'The decline tracks compiler hardening and allocator changes more closely than it tracks language rewrites. Both matter; only one scales to existing code.',
		'days_ago' => 15,
		'blocks'   => array(
			array( 'p' => 'Memory-safety vulnerabilities as a share of reported CVEs have been declining in several large codebases. The convenient explanation is that new code is being written in memory-safe languages. The data supports a more mundane contributor: existing C and C++ got meaningfully harder to exploit.' ),
			array( 'h2' => 'What hardening actually did' ),
			array( 'p' => 'A series of changes, each individually modest, compounded:' ),
			array(
				'list' => array(
					'Compiler bounds checking on standard-library containers, enabled by default in release builds.',
					'Hardened allocators with guard pages, randomised placement and free-list integrity checks.',
					'Control-flow integrity, which turns many corruption bugs into crashes rather than code execution.',
					'Automatic variable initialisation, which eliminated a whole class of uninitialised-read bugs outright.',
				),
			),
			array( 'quote' => 'A crash is a bug report. Code execution is an incident. Most hardening converts the second into the first.' ),
			array( 'h2' => 'The new-code effect is real too' ),
			array( 'p' => 'Where large projects have adopted memory-safe languages for new components, the vulnerability curve in those components is close to flat. The effect is clearest in exactly the places the theory predicts: parsers, decoders and anything handling untrusted input.' ),
			array( 'p' => 'That is a strong argument for the language choice in new work. It is not an argument that applies to the several decades of existing code that is not going to be rewritten.' ),
			array( 'h2' => 'The cost side' ),
			array( 'p' => 'Hardening is not free. Bounds checking costs cycles, control-flow integrity costs binary size and indirect-call performance, and allocator hardening costs memory. In most workloads the aggregate is small enough that nobody notices. In the ones where it is not, teams disable it selectively — and those selective disables are where the remaining bugs concentrate.' ),
			array( 'h2' => 'Reading the numbers carefully' ),
			array( 'p' => 'One caution on the trend: CVE counts measure what is reported, not what exists. Some of the decline could reflect shifting researcher attention toward logic bugs and supply-chain attacks, which are currently easier to find and more valuable. The hardening effect is well supported by exploitability analysis; the magnitude of the decline is less certain than the direction.' ),
		),
	),

);

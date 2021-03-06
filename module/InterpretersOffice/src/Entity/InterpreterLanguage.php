<?php

/** module/InterpretersOffice/src/Entity/InterpreterLanguage.php   */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing an Interpreter's Language.
 *
 * Technically, it is a language *pair*, but in this system it is understood that
 * the other language of the pair is English. There is a many-to-many relationship
 * between interpreters and languages. But because there is also metadata to record
 * about the language (federal certification), it is implemented as a Many-To-One
 * relationship on either side.
 *
 * @ORM\Entity
 * @ORM\Table(name="interpreters_languages")
 */
class InterpreterLanguage
{
    /**
     * constructor.
     *
     * @param Interpreter $interpreter
     * @param Language    $language
     *
     */
    public function __construct(
        Interpreter $interpreter = null,
        Language $language = null
    ) {
        $this->interpreter = $interpreter;
        $this->language = $language;
    }

    /**
     * The Interpreter who works in this language.
     *
     * @ORM\ManyToOne(targetEntity="Interpreter",inversedBy="interpreterLanguages")
     * @ORM\Id
     *
     * @var Interpreter
     */
    protected $interpreter;

    /**
     * The language in which this interpreter works.
     *
     * @ORM\ManyToOne(targetEntity="Language",inversedBy="interpreterLanguages",fetch="EAGER")
     * @ORM\Id
     *
     * @var Language
     */
    protected $language;

    /**
    *  language credential
    *
    *  @ORM\ManyToOne(targetEntity="LanguageCredential")
    *  @ORM\JoinColumn(nullable=true,name="credential_id")
    *
    *  @var LanguageCredential
    */
    protected $languageCredential;

    /**
     * Set interpreter.
     *
     * @param \InterpretersOffice\Entity\Interpreter $interpreter
     *
     * @return InterpreterLanguage
     */
    public function setInterpreter(Interpreter $interpreter = null)
    {
        $this->interpreter = $interpreter;

        return $this;
    }

    /**
     * Get interpreter.
     *
     * @return Interpreter
     */
    public function getInterpreter()
    {
        return $this->interpreter;
    }

    /**
     * Set language.
     *
     * @param Language $language
     *
     * @return InterpreterLanguage
     */
    public function setLanguage(Language $language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }


    /**
     * return this entity as an array [ language_id => federalCertification ].
     *
     * @todo see if we really need this at all?
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'language_id' => $this->language->getId(),
            //'federalCertification' => $this->getFederalCertification(),
        ];
    }

    /**
     * is the language among the federal certification languages?
     * @throws \RuntimeException
     * @return boolean
     */
    public function isCertifiable()
    {
        $language = $this->getLanguage();
        if (! $language) {
            throw new \RuntimeException('language entity must be set before calling '.__FUNCTION__);
        }
        return $language->isFederallyCertified();
    }

    /**
     * Set languageCredential.
     *
     * @param LanguageCredential|null $languageCredential
     *
     * @return InterpreterLanguage
     */
    public function setLanguageCredential(LanguageCredential $languageCredential = null)
    {
        $this->languageCredential = $languageCredential;

        return $this;
    }

    /**
     * Get languageCredential.
     *
     * @return LanguageCredential|null
     */
    public function getLanguageCredential()
    {
        return $this->languageCredential;
    }
}
